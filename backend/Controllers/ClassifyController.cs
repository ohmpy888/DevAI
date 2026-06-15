using Microsoft.AspNetCore.Mvc;
using Microsoft.ML.OnnxRuntime;
using Microsoft.ML.OnnxRuntime.Tensors;
using SixLabors.ImageSharp;
using SixLabors.ImageSharp.PixelFormats;
using SixLabors.ImageSharp.Processing;
using System.Diagnostics;

namespace ImageClassifierBackend.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class ClassifyController : ControllerBase
    {
        private static readonly InferenceSession _nudeSession;
        private static readonly InferenceSession _violenceSession;

        private static readonly string[] NudeLabels = { "normal", "nude", "sexy" };
        private static readonly string[] ViolenceLabels = { "normal", "violence" };

        static ClassifyController()
        {

            string nudePath = FindModelPath("best_nude_classification.onnx");
            string violencePath = FindModelPath("best_violence_classification.onnx");

            Console.WriteLine($"[INFO] Loading Nude model from: {nudePath}");
            _nudeSession = new InferenceSession(nudePath);

            Console.WriteLine($"[INFO] Loading Violence model from: {violencePath}");
            _violenceSession = new InferenceSession(violencePath);
        }

        private static string FindModelPath(string modelName)
        {
            var paths = new[]
            {
                Path.Combine(AppContext.BaseDirectory, modelName),
                Path.Combine(AppContext.BaseDirectory, "Models", modelName),
                Path.Combine(Directory.GetCurrentDirectory(), "Models", modelName),
                Path.Combine(Directory.GetCurrentDirectory(), "backend", "Models", modelName),
                Path.Combine(Directory.GetCurrentDirectory(), "..", "backend", "Models", modelName)
            };

            foreach (var path in paths)
            {
                if (System.IO.File.Exists(path))
                {
                    return path;
                }
            }
            throw new FileNotFoundException($"Model file '{modelName}' not found in any standard path.");
        }

        [HttpPost]
        public async Task<IActionResult> ClassifyImage(IFormFile file)
        {
            if (file == null || file.Length == 0)
            {
                return BadRequest(new { error = "No image file provided." });
            }

            try
            {
                var stopwatch = Stopwatch.StartNew();

                Image<Rgb24> image;
                try
                {
                    using var stream = file.OpenReadStream();
                    image = await Image.LoadAsync<Rgb24>(stream);
                }
                catch (Exception)
                {
                    return BadRequest(new { error = "ไม่สามารถอ่านไฟล์รูปภาพได้ รูปแบบไฟล์อาจไม่รองรับ หรือไฟล์รูปภาพเสียหาย (โปรดใช้ไฟล์ JPG, PNG หรือ WebP)" });
                }

                using (image)
                {
                    image.Mutate(x => x.Resize(new ResizeOptions
                    {
                        Size = new Size(320, 320),
                        Mode = ResizeMode.Stretch
                    }));

                    var inputTensor = new DenseTensor<float>(new[] { 1, 3, 320, 320 });
                    for (int y = 0; y < 320; y++)
                    {
                        for (int x = 0; x < 320; x++)
                        {
                            Rgb24 pixel = image[x, y];
                            inputTensor[0, 0, y, x] = pixel.R / 255.0f; 
                            inputTensor[0, 1, y, x] = pixel.G / 255.0f; 
                            inputTensor[0, 2, y, x] = pixel.B / 255.0f; 
                        }
                    }

                    var nudeResults = RunInference(_nudeSession, inputTensor, NudeLabels);
                    var violenceResults = RunInference(_violenceSession, inputTensor, ViolenceLabels);

                    stopwatch.Stop();

                    bool isNude = nudeResults.Label == "nude" && nudeResults.Confidence > 0.5f;
                    bool isSexy = nudeResults.Label == "sexy" && nudeResults.Confidence > 0.6f;
                    bool isViolence = violenceResults.Label == "violence" && violenceResults.Confidence > 0.5f;

                    bool isSensitive = isNude || isSexy || isViolence;
                    string reason = "Safe";
                    float confidence = 0.0f;

                    if (isNude)
                    {
                        reason = "Nude";
                        confidence = nudeResults.Confidence;
                    }
                    else if (isViolence)
                    {
                        reason = "Violence";
                        confidence = violenceResults.Confidence;
                    }
                    else if (isSexy)
                    {
                        reason = "Sexy (Suggestive)";
                        confidence = nudeResults.Confidence;
                    }
                    else
                    {
                        confidence = Math.Max(nudeResults.Probabilities["normal"], violenceResults.Probabilities["normal"]);
                    }

                    var response = new
                    {
                        isSensitive,
                        reason,
                        confidence = Math.Round(confidence, 4),
                        inferenceTimeMs = stopwatch.ElapsedMilliseconds,
                        nudeModel = new
                        {
                            predictedLabel = nudeResults.Label,
                            confidence = Math.Round(nudeResults.Confidence, 4),
                            probabilities = nudeResults.Probabilities.ToDictionary(k => k.Key, v => Math.Round(v.Value, 4))
                        },
                        violenceModel = new
                        {
                            predictedLabel = violenceResults.Label,
                            confidence = Math.Round(violenceResults.Confidence, 4),
                            probabilities = violenceResults.Probabilities.ToDictionary(k => k.Key, v => Math.Round(v.Value, 4))
                        }
                    };

                    return Ok(response);
                }
            }
            catch (Exception ex)
            {
                return StatusCode(500, new { error = $"Internal server error during classification: {ex.Message}" });
            }
        }

        private static InferenceOutput RunInference(InferenceSession session, DenseTensor<float> inputTensor, string[] labels)
        {
            string inputName = session.InputMetadata.Keys.First();
            var inputs = new List<NamedOnnxValue>
            {
                NamedOnnxValue.CreateFromTensor(inputName, inputTensor)
            };

            using var results = session.Run(inputs);
            var outputTensor = results.First().AsTensor<float>();
            float[] logits = outputTensor.ToArray();

            float[] probs = Softmax(logits);

            var probabilities = new Dictionary<string, float>();
            int maxIdx = 0;
            float maxProb = -1.0f;

            for (int i = 0; i < labels.Length && i < probs.Length; i++)
            {
                probabilities[labels[i]] = probs[i];
                if (probs[i] > maxProb)
                {
                    maxProb = probs[i];
                    maxIdx = i;
                }
            }

            return new InferenceOutput
            {
                Label = labels[maxIdx],
                Confidence = maxProb,
                Probabilities = probabilities
            };
        }

        private static float[] Softmax(float[] values)
        {
            float maxVal = values.Max();
            float[] expValues = values.Select(v => MathF.Exp(v - maxVal)).ToArray();
            float sum = expValues.Sum();
            return expValues.Select(v => v / sum).ToArray();
        }

        private class InferenceOutput
        {
            public string Label { get; set; } = string.Empty;
            public float Confidence { get; set; }
            public Dictionary<string, float> Probabilities { get; set; } = new();
        }
    }
}
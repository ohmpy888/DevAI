<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafeVision AI - Content Moderation Dashboard</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Outfit', 'Sarabun', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>

    .glass-panel {
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }

    .glass-card {
      background: rgba(30, 41, 59, 0.4);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    ::-webkit-scrollbar {
      width: 6px;
    }
    ::-webkit-scrollbar-track {
      background: rgba(15, 23, 42, 0.1);
    }
    ::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.25);
    }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen font-sans flex flex-col relative overflow-x-hidden select-none">

  <div id="warning-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-[9999] transition-all duration-300">
    <div class="glass-panel max-w-lg w-full rounded-3xl p-6 md:p-8 border border-white/10 flex flex-col gap-6 text-center">
      <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 mx-auto text-2xl animate-pulse">
        <i class="fa-solid fa-circle-exclamation"></i>
      </div>
      <div>
        <h3 class="text-xl font-bold text-white mb-3">คำชี้แจงการใช้งานระบบ AI</h3>
        <p class="text-sm text-slate-300 leading-relaxed">
          ชุดข้อมูล (Dataset) ที่นำมาใช้ในการฝึกสอนโมเดล AI ชุดนี้ <strong>ส่วนใหญ่ประกอบไปด้วยภาพของมนุษย์และสัตว์บางชนิด</strong>
        </p>
        <p class="text-sm text-slate-400 mt-2 leading-relaxed">
          ดังนั้น หากท่านอัปโหลดรูปภาพประเภทอื่นๆ ที่ไม่ได้อยู่ในกลุ่มข้อมูลดังกล่าว ผลการวิเคราะห์และการจำแนกประเภทภาพอาจจะมีความคลาดเคลื่อนหรือผิดพลาดได้
        </p>
      </div>
      <button id="btn-close-modal" class="mt-2 w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-sm font-semibold tracking-wide text-white transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98]">
        ฉันรับทราบและเข้าใจแล้ว เข้าสู่หน้าเว็บ
      </button>
    </div>
  </div>

  <canvas id="shader-canvas" class="fixed top-0 left-0 w-full h-full -z-10 pointer-events-none opacity-85"></canvas>

  <header class="w-full py-5 px-6 md:px-12 flex items-center justify-between border-b border-white/5 backdrop-blur-md bg-slate-950/25 sticky top-0 z-50">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
        <i class="fa-solid fa-shield-halved text-white text-lg"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">SafeVision AI</h1>
        <p class="text-[10px] text-indigo-400/80 uppercase font-semibold tracking-widest">Content Safety Guard</p>
      </div>
    </div>
    <div class="flex items-center gap-4 text-xs text-slate-400">
      <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/15">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        C# API Connected
      </span>
    </div>
  </header>

  <main class="flex-grow flex items-center justify-center p-4 md:p-8">
    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

      <section class="lg:col-span-7 flex flex-col gap-6">
        <div class="glass-panel rounded-3xl p-6 md:p-8 flex flex-col gap-6 h-full min-h-[480px]">

          <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <i class="fa-solid fa-cloud-arrow-up text-violet-400"></i>
              ทดสอบภาพถ่ายของคุณ
            </h2>
            <span class="text-xs text-slate-400">รองรับไฟล์ JPG, PNG, WEBP</span>
          </div>

          <div id="dropzone" class="flex-grow border-2 border-dashed border-slate-700 hover:border-violet-500/50 hover:bg-violet-950/10 rounded-2xl flex flex-col items-center justify-center p-8 transition-all duration-300 cursor-pointer group text-center relative overflow-hidden min-h-[300px]">
            <input type="file" id="file-input" class="hidden" accept="image/*">

            <div id="dropzone-idle" class="flex flex-col items-center gap-4 transition-all duration-300">
              <div class="w-16 h-16 rounded-full bg-slate-800/80 group-hover:bg-violet-900/20 flex items-center justify-center transition-all duration-300 border border-white/5 group-hover:border-violet-500/30">
                <i class="fa-solid fa-image text-slate-400 group-hover:text-violet-400 text-2xl transition-all duration-300"></i>
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-200">ลากและวางรูปภาพของคุณที่นี่</p>
                <p class="text-xs text-slate-400 mt-1">หรือคลิกเพื่อเลือกไฟล์จากอุปกรณ์</p>
              </div>
            </div>

            <div id="preview-container" class="hidden absolute inset-0 w-full h-full bg-slate-900 flex items-center justify-center z-10">
              <img id="image-preview" src="" alt="Uploaded image" class="w-full h-full object-contain transition-all duration-500">

              <div id="blur-overlay" class="hidden absolute inset-0 bg-slate-950/80 backdrop-blur-3xl flex flex-col items-center justify-center p-6 text-center transition-all duration-500 z-20">
                <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/30 flex items-center justify-center mb-4 text-red-500 shadow-lg shadow-red-500/10 animate-bounce">
                  <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-red-400 mb-2">ตรวจพบภาพไม่เหมาะสม</h3>
                <p class="text-xs text-slate-300 max-w-sm mb-6" id="blur-warning-text">
                  รูปภาพนี้ถูกบุกเบิกเนื่องจากตรวจพบเนื้อหาประเภท Nude หรือ Violence
                </p>
                <div class="flex gap-4">
                  <button id="btn-reveal" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-xs font-semibold flex items-center gap-2 transition-all shadow-md shadow-red-600/20">
                    <i class="fa-solid fa-eye"></i> แสดงภาพชั่วคราว
                  </button>
                  <button id="btn-re-upload" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold flex items-center gap-2 transition-all border border-white/5">
                    <i class="fa-solid fa-rotate-left"></i> เปลี่ยนรูปภาพ
                  </button>
                </div>
              </div>

              <div id="preview-floating-bar" class="absolute bottom-4 left-1/2 -translate-x-1/2 px-4 py-2 rounded-full glass-card flex items-center gap-4 border border-white/10 z-30 opacity-90 hover:opacity-100 transition-all">
                <button id="btn-toggle-eye" class="text-slate-300 hover:text-white text-xs flex items-center gap-1.5 focus:outline-none">
                  <i id="eye-icon" class="fa-solid fa-eye-slash text-red-400"></i>
                  <span id="eye-text">ซ่อนภาพ</span>
                </button>
                <div class="w-px h-4 bg-slate-700"></div>
                <button id="btn-change-image" class="text-slate-300 hover:text-white text-xs flex items-center gap-1.5 focus:outline-none">
                  <i class="fa-solid fa-arrow-up-from-bracket"></i>
                  <span>เลือกภาพใหม่</span>
                </button>
              </div>
            </div>

            <div id="loading-container" class="hidden absolute inset-0 w-full h-full bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center z-25 transition-all">
              <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-violet-500/10"></div>
                <div class="absolute inset-0 rounded-full border-4 border-violet-500 border-t-transparent animate-spin"></div>
              </div>
              <p class="text-sm font-semibold text-violet-300 animate-pulse">กำลังประมวลผลด้วย AI...</p>
              <p class="text-xs text-slate-400 mt-1">C# ONNX Runtime กำลังคำนวณข้อมูล</p>
            </div>

          </div>

        </div>
      </section>

      <section class="lg:col-span-5 flex flex-col gap-6">
        <div class="glass-panel rounded-3xl p-6 md:p-8 flex flex-col gap-6 h-full justify-between">
          <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <i class="fa-solid fa-square-poll-horizontal text-indigo-400"></i>
              ผลวิเคราะห์จาก AI
            </h2>
          </div>

          <div id="results-empty" class="flex-grow flex flex-col items-center justify-center p-8 text-center text-slate-500 min-h-[300px] transition-all">
            <div class="w-16 h-16 rounded-2xl border border-dashed border-slate-800 flex items-center justify-center mb-4">
              <i class="fa-solid fa-chart-line text-2xl text-slate-600"></i>
            </div>
            <p class="text-sm font-medium">ยังไม่มีข้อมูลการวิเคราะห์</p>
            <p class="text-xs text-slate-600 mt-1">อัปโหลดรูปภาพเพื่อตรวจเช็กเนื้อหา</p>
          </div>

          <div id="results-active" class="hidden flex-grow flex flex-col gap-6 transition-all duration-300">

            <div id="badge-overall" class="rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 border">
              <div id="badge-icon-box" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md">
                <i id="badge-icon" class="fa-solid fa-circle-check"></i>
              </div>
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">สถานะผลการตรวจจับ</p>
                <h3 id="badge-status-title" class="text-lg font-bold">ปลอดภัย (Safe Content)</h3>
              </div>
            </div>

            <div class="glass-card rounded-2xl p-5 border border-white/5">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-xs font-bold text-violet-400 uppercase tracking-widest flex items-center gap-1.5">
                  <i class="fa-solid fa-venus-mars text-xs"></i>
                  Nude Classifier Model
                </h4>
                <span id="nude-model-prediction" class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 border border-white/5 font-semibold text-slate-300">Normal</span>
              </div>
              <div class="flex flex-col gap-3">

                <div>
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-400">Normal (ปลอดภัย)</span>
                    <span id="val-nude-normal" class="font-semibold text-slate-200">0%</span>
                  </div>
                  <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                    <div id="bar-nude-normal" class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-400">Sexy (วาบหวิว)</span>
                    <span id="val-nude-sexy" class="font-semibold text-slate-200">0%</span>
                  </div>
                  <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                    <div id="bar-nude-sexy" class="bg-gradient-to-r from-amber-500 to-orange-400 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-400">Nude (เปลือยกาย)</span>
                    <span id="val-nude-nude" class="font-semibold text-slate-200">0%</span>
                  </div>
                  <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                    <div id="bar-nude-nude" class="bg-gradient-to-r from-red-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="glass-card rounded-2xl p-5 border border-white/5">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-xs font-bold text-rose-400 uppercase tracking-widest flex items-center gap-1.5">
                  <i class="fa-solid fa-burst text-xs"></i>
                  Violence Classifier Model
                </h4>
                <span id="violence-model-prediction" class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 border border-white/5 font-semibold text-slate-300">Normal</span>
              </div>
              <div class="flex flex-col gap-3">

                <div>
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-400">Normal (ปลอดภัย)</span>
                    <span id="val-violence-normal" class="font-semibold text-slate-200">0%</span>
                  </div>
                  <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                    <div id="bar-violence-normal" class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-400">Violence (ความรุนแรง)</span>
                    <span id="val-violence-violence" class="font-semibold text-slate-200">0%</span>
                  </div>
                  <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                    <div id="bar-violence-violence" class="bg-gradient-to-r from-red-500 to-rose-600 h-full rounded-full transition-all duration-500" style="width: 0%;"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="border-t border-white/5 pt-4 mt-auto">
            <div class="flex items-center justify-between text-[11px] text-slate-500">
              <span id="inference-time-label" class="flex items-center gap-1">
                ความเร็วประมวลผล: <strong id="val-inference-time" class="text-slate-300 font-semibold">0ms</strong>
              </span>
              <span class="flex items-center gap-1">
                <i class="fa-solid fa-server"></i>
                Host: <strong class="text-slate-300 font-semibold">ASP.NET 8.0 + ONNX</strong>
              </span>
            </div>
          </div>

        </div>
      </section>

    </div>
  </main>

  <footer class="w-full py-6 text-center text-xs text-slate-600 border-t border-white/5 backdrop-blur-md bg-slate-950/30">
    <p>SafeVision AI - พัฒนาด้วย C# .NET Core, ONNX Runtime และ PHP &copy; 2026. All rights reserved.</p>
  </footer>

  <script>

    function initShaderBackground() {
      const canvas = document.getElementById("shader-canvas");
      if (!canvas) return;

      const gl = canvas.getContext("webgl");
      if (!gl) {
        console.warn("WebGL not supported in this browser.");
        return;
      }

      const vsSource = `
        attribute vec4 aVertexPosition;
        void main() {
          gl_Position = aVertexPosition;
        }
      `;

      const fsSource = `
        precision highp float;
        uniform vec2 iResolution;
        uniform float iTime;

        const float overallSpeed = 0.2;
        const float gridSmoothWidth = 0.015;
        const float axisWidth = 0.05;
        const float majorLineWidth = 0.025;
        const float minorLineWidth = 0.0125;
        const float majorLineFrequency = 5.0;
        const float minorLineFrequency = 1.0;
        const vec4 gridColor = vec4(0.5);
        const float scale = 5.0;
        const vec4 lineColor = vec4(0.4, 0.2, 0.8, 1.0);
        const float minLineWidth = 0.01;
        const float maxLineWidth = 0.2;
        const float lineSpeed = 1.0 * overallSpeed;
        const float lineAmplitude = 1.0;
        const float lineFrequency = 0.2;
        const float warpSpeed = 0.2 * overallSpeed;
        const float warpFrequency = 0.5;
        const float warpAmplitude = 1.0;
        const float offsetFrequency = 0.5;
        const float offsetSpeed = 1.33 * overallSpeed;
        const float minOffsetSpread = 0.6;
        const float maxOffsetSpread = 2.0;
        const int linesPerGroup = 16;

        #define drawCircle(pos, radius, coord) smoothstep(radius + gridSmoothWidth, radius, length(coord - (pos)))
        #define drawSmoothLine(pos, halfWidth, t) smoothstep(halfWidth, 0.0, abs(pos - (t)))
        #define drawCrispLine(pos, halfWidth, t) smoothstep(halfWidth + gridSmoothWidth, halfWidth, abs(pos - (t)))
        #define drawPeriodicLine(freq, width, t) drawCrispLine(freq / 2.0, width, abs(mod(t, freq) - (freq) / 2.0))

        float drawGridLines(float axis) {
          return drawCrispLine(0.0, axisWidth, axis)
                + drawPeriodicLine(majorLineFrequency, majorLineWidth, axis)
                + drawPeriodicLine(minorLineFrequency, minorLineWidth, axis);
        }

        float drawGrid(vec2 space) {
          return min(1.0, drawGridLines(space.x) + drawGridLines(space.y));
        }

        float random(float t) {
          return (cos(t) + cos(t * 1.3 + 1.3) + cos(t * 1.4 + 1.4)) / 3.0;
        }

        float getPlasmaY(float x, float horizontalFade, float offset) {
          return random(x * lineFrequency + iTime * lineSpeed) * horizontalFade * lineAmplitude + offset;
        }

        void main() {
          vec2 fragCoord = gl_FragCoord.xy;
          vec4 fragColor;
          vec2 uv = fragCoord.xy / iResolution.xy;
          vec2 space = (fragCoord - iResolution.xy / 2.0) / iResolution.x * 2.0 * scale;

          float horizontalFade = 1.0 - (cos(uv.x * 6.28) * 0.5 + 0.5);
          float verticalFade = 1.0 - (cos(uv.y * 6.28) * 0.5 + 0.5);

          space.y += random(space.x * warpFrequency + iTime * warpSpeed) * warpAmplitude * (0.5 + horizontalFade);
          space.x += random(space.y * warpFrequency + iTime * warpSpeed + 2.0) * warpAmplitude * horizontalFade;

          vec4 lines = vec4(0.0);
          vec4 bgColor1 = vec4(0.03, 0.02, 0.08, 1.0); 
          vec4 bgColor2 = vec4(0.08, 0.03, 0.15, 1.0); 

          for(int l = 0; l < linesPerGroup; l++) {
            float normalizedLineIndex = float(l) / float(linesPerGroup);
            float offsetTime = iTime * offsetSpeed;
            float offsetPosition = float(l) + space.x * offsetFrequency;
            float rand = random(offsetPosition + offsetTime) * 0.5 + 0.5;
            float halfWidth = mix(minLineWidth, maxLineWidth, rand * horizontalFade) / 2.0;
            float offset = random(offsetPosition + offsetTime * (1.0 + normalizedLineIndex)) * mix(minOffsetSpread, maxOffsetSpread, horizontalFade);
            float linePosition = getPlasmaY(space.x, horizontalFade, offset);
            float line = drawSmoothLine(linePosition, halfWidth, space.y) / 2.0 + drawCrispLine(linePosition, halfWidth * 0.15, space.y);

            float circleX = mod(float(l) + iTime * lineSpeed, 25.0) - 12.0;
            vec2 circlePosition = vec2(circleX, getPlasmaY(circleX, horizontalFade, offset));
            float circle = drawCircle(circlePosition, 0.01, space) * 4.0;

            line = line + circle;
            lines += line * lineColor * rand;
          }

          fragColor = mix(bgColor1, bgColor2, uv.x);
          fragColor *= verticalFade;
          fragColor.a = 1.0;
          fragColor += lines;

          gl_FragColor = fragColor;
        }
      `;

      const loadShader = (gl, type, source) => {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
          console.error("Shader compile error: ", gl.getShaderInfoLog(shader));
          gl.deleteShader(shader);
          return null;
        }
        return shader;
      };

      const initShaderProgram = (gl, vsSource, fsSource) => {
        const vertexShader = loadShader(gl, gl.VERTEX_SHADER, vsSource);
        const fragmentShader = loadShader(gl, gl.FRAGMENT_SHADER, fsSource);

        const shaderProgram = gl.createProgram();
        gl.attachShader(shaderProgram, vertexShader);
        gl.attachShader(shaderProgram, fragmentShader);
        gl.linkProgram(shaderProgram);

        if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
          console.error("Shader program link error: ", gl.getProgramInfoLog(shaderProgram));
          return null;
        }
        return shaderProgram;
      };

      const shaderProgram = initShaderProgram(gl, vsSource, fsSource);
      if (!shaderProgram) return;

      const positionBuffer = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
      const positions = [-1.0, -1.0, 1.0, -1.0, -1.0, 1.0, 1.0, 1.0];
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(positions), gl.STATIC_DRAW);

      const programInfo = {
        program: shaderProgram,
        attribLocations: {
          vertexPosition: gl.getAttribLocation(shaderProgram, "aVertexPosition"),
        },
        uniformLocations: {
          resolution: gl.getUniformLocation(shaderProgram, "iResolution"),
          time: gl.getUniformLocation(shaderProgram, "iTime"),
        },
      };

      const resizeCanvas = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        gl.viewport(0, 0, canvas.width, canvas.height);
      };

      window.addEventListener("resize", resizeCanvas);
      resizeCanvas();

      let startTime = Date.now();
      const render = () => {
        const currentTime = (Date.now() - startTime) / 1000;

        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.clear(gl.COLOR_BUFFER_BIT);

        gl.useProgram(programInfo.program);

        gl.uniform2f(
          programInfo.uniformLocations.resolution,
          canvas.width,
          canvas.height
        );
        gl.uniform1f(programInfo.uniformLocations.time, currentTime);

        gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
        gl.vertexAttribPointer(
          programInfo.attribLocations.vertexPosition,
          2,
          gl.FLOAT,
          false,
          0,
          0
        );
        gl.enableVertexAttribArray(programInfo.attribLocations.vertexPosition);

        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        requestAnimationFrame(render);
      };

      requestAnimationFrame(render);
    }

    initShaderBackground();

    const API_URL = "http://localhost:5100/api/classify";

    const dropzone = document.getElementById("dropzone");
    const fileInput = document.getElementById("file-input");
    const dropzoneIdle = document.getElementById("dropzone-idle");
    const previewContainer = document.getElementById("preview-container");
    const imagePreview = document.getElementById("image-preview");
    const blurOverlay = document.getElementById("blur-overlay");
    const blurWarningText = document.getElementById("blur-warning-text");
    const loadingContainer = document.getElementById("loading-container");

    const btnReveal = document.getElementById("btn-reveal");
    const btnReUpload = document.getElementById("btn-re-upload");
    const btnChangeImage = document.getElementById("btn-change-image");
    const btnToggleEye = document.getElementById("btn-toggle-eye");
    const eyeIcon = document.getElementById("eye-icon");
    const eyeText = document.getElementById("eye-text");

    const resultsEmpty = document.getElementById("results-empty");
    const resultsActive = document.getElementById("results-active");
    const badgeOverall = document.getElementById("badge-overall");
    const badgeIconBox = document.getElementById("badge-icon-box");
    const badgeIcon = document.getElementById("badge-icon");
    const badgeStatusTitle = document.getElementById("badge-status-title");

    const nudePredictLabel = document.getElementById("nude-model-prediction");
    const valNudeNormal = document.getElementById("val-nude-normal");
    const barNudeNormal = document.getElementById("bar-nude-normal");
    const valNudeSexy = document.getElementById("val-nude-sexy");
    const barNudeSexy = document.getElementById("bar-nude-sexy");
    const valNudeNude = document.getElementById("val-nude-nude");
    const barNudeNude = document.getElementById("bar-nude-nude");

    const violencePredictLabel = document.getElementById("violence-model-prediction");
    const valViolenceNormal = document.getElementById("val-violence-normal");
    const barViolenceNormal = document.getElementById("bar-violence-normal");
    const valViolenceViolence = document.getElementById("val-violence-violence");
    const barViolenceViolence = document.getElementById("bar-violence-violence");

    const valInferenceTime = document.getElementById("val-inference-time");

    let isImageCurrentlyBlurred = false;
    let isSensitiveDetected = false;
    let currentResponseData = null;

    dropzone.addEventListener("click", (e) => {

      if (e.target.closest("button") || e.target.closest("#preview-floating-bar")) return;
      fileInput.click();
    });

    ["dragenter", "dragover"].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.add("border-violet-500", "bg-violet-950/15");
      }, false);
    });

    ["dragleave", "drop"].forEach(eventName => {
      dropzone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropzone.classList.remove("border-violet-500", "bg-violet-950/15");
      }, false);
    });

    dropzone.addEventListener("drop", (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files.length > 0) {
        handleFileSelect(files[0]);
      }
    });

    fileInput.addEventListener("change", (e) => {
      if (fileInput.files.length > 0) {
        handleFileSelect(fileInput.files[0]);
      }
    });

    btnReUpload.addEventListener("click", resetUpload);
    btnChangeImage.addEventListener("click", resetUpload);

    btnReveal.addEventListener("click", () => {
      unblurPreview();
    });

    btnToggleEye.addEventListener("click", () => {
      if (isImageCurrentlyBlurred) {
        unblurPreview();
      } else {
        blurPreview();
      }
    });

    function resetUpload() {
      fileInput.value = "";
      imagePreview.src = "";
      previewContainer.classList.add("hidden");
      dropzoneIdle.classList.remove("hidden");
      blurOverlay.classList.add("hidden");

      resultsActive.classList.add("hidden");
      resultsEmpty.classList.remove("hidden");

      valInferenceTime.innerText = "0ms";
      isImageCurrentlyBlurred = false;
      isSensitiveDetected = false;
      currentResponseData = null;
    }

    function handleFileSelect(file) {
      if (!file.type.startsWith('image/')) {
        alert("กรุณาเลือกไฟล์รูปภาพเท่านั้น!");
        return;
      }

      const objectUrl = URL.createObjectURL(file);
      imagePreview.src = objectUrl;

      dropzoneIdle.classList.add("hidden");
      previewContainer.classList.remove("hidden");
      blurOverlay.classList.add("hidden");

      imagePreview.className = "w-full h-full object-contain transition-all duration-500";

      loadingContainer.classList.remove("hidden");
      resultsActive.classList.add("hidden");
      resultsEmpty.classList.remove("hidden");

      const formData = new FormData();
      formData.append("file", file);

      fetch(API_URL, {
        method: "POST",
        body: formData
      })
      .then(async response => {
        if (!response.ok) {
          const errData = await response.json().catch(() => ({}));
          throw new Error(errData.error || ("HTTP error " + response.status));
        }
        return response.json();
      })
      .then(data => {
        loadingContainer.classList.add("hidden");
        displayResults(data);
      })
      .catch(error => {
        console.error("Inference Error:", error);
        loadingContainer.classList.add("hidden");
        alert("เกิดข้อผิดพลาดในการประมวลผล: " + error.message);
        resetUpload();
      });
    }

    function displayResults(data) {
      currentResponseData = data;
      isSensitiveDetected = data.isSensitive;

      resultsEmpty.classList.add("hidden");
      resultsActive.classList.remove("hidden");

      valInferenceTime.innerText = data.inferenceTimeMs + "ms";

      nudePredictLabel.innerText = capitalizeFirst(data.nudeModel.predictedLabel);

      const nudeProbs = data.nudeModel.probabilities;
      updateProgressBar("nude-normal", nudeProbs.normal);
      updateProgressBar("nude-sexy", nudeProbs.sexy);
      updateProgressBar("nude-nude", nudeProbs.nude);

      violencePredictLabel.innerText = capitalizeFirst(data.violenceModel.predictedLabel);

      const violenceProbs = data.violenceModel.probabilities;
      updateProgressBar("violence-normal", violenceProbs.normal);
      updateProgressBar("violence-violence", violenceProbs.violence);

      if (data.isSensitive) {

        blurPreview();

        let reasonThai = data.reason === "Nude" ? "ภาพเปลือยกาย (Nude Content)" : 
                         data.reason === "Violence" ? "ความรุนแรง (Violence Content)" : 
                         "เนื้อหาวาบหวิว (Sexy Content)";
        let confidencePercent = Math.round(data.confidence * 100);
        blurWarningText.innerText = `รูปภาพนี้ถูกบดบังเพื่อความปลอดภัยเนื่องจากตรวจพบ ${reasonThai} ด้วยความมั่นใจ ${confidencePercent}%`;

        badgeOverall.className = "rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 bg-red-950/20 border-red-500/30 text-red-400";
        badgeIconBox.className = "w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md bg-red-500/20 text-red-400 shadow-red-500/10";
        badgeIcon.className = "fa-solid fa-triangle-exclamation";
        badgeStatusTitle.innerText = `พบเนื้อหาไม่เหมาะสม: ${data.reason}`;
      } else {

        unblurPreview();
        blurOverlay.classList.add("hidden"); 

        badgeOverall.className = "rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 bg-emerald-950/20 border-emerald-500/30 text-emerald-400";
        badgeIconBox.className = "w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-md bg-emerald-500/20 text-emerald-400 shadow-emerald-500/10";
        badgeIcon.className = "fa-solid fa-circle-check";
        badgeStatusTitle.innerText = "ปลอดภัย (Safe Content)";
      }
    }

    function blurPreview() {
      imagePreview.classList.add("blur-2xl", "brightness-75");
      blurOverlay.classList.remove("hidden");
      isImageCurrentlyBlurred = true;

      eyeIcon.className = "fa-solid fa-eye text-emerald-400";
      eyeText.innerText = "เปิดเผยภาพ";
      btnToggleEye.className = "text-slate-300 hover:text-white text-xs flex items-center gap-1.5 focus:outline-none";
    }

    function unblurPreview() {
      imagePreview.classList.remove("blur-2xl", "brightness-75");
      blurOverlay.classList.add("hidden");
      isImageCurrentlyBlurred = false;

      if (isSensitiveDetected) {
        eyeIcon.className = "fa-solid fa-eye-slash text-red-400";
        eyeText.innerText = "ซ่อนภาพ";
      } else {

        eyeIcon.className = "fa-solid fa-circle-check text-emerald-400";
        eyeText.innerText = "ปลอดภัย";
      }
    }

    function updateProgressBar(idPrefix, valFloat) {
      const percentage = Math.round(valFloat * 100);
      const valElement = document.getElementById(`val-${idPrefix}`);
      const barElement = document.getElementById(`bar-${idPrefix}`);

      if (valElement) valElement.innerText = percentage + "%";
      if (barElement) barElement.style.width = percentage + "%";
    }

    function capitalizeFirst(str) {
      if (!str) return "";
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    const warningModal = document.getElementById("warning-modal");
    const btnCloseModal = document.getElementById("btn-close-modal");
    if (warningModal && btnCloseModal) {
      btnCloseModal.addEventListener("click", () => {
        warningModal.classList.add("opacity-0", "pointer-events-none");
        setTimeout(() => warningModal.classList.add("hidden"), 300);
      });
    }
  </script>
</body>
</html>
var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllers();

builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowAll", policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

builder.WebHost.ConfigureKestrel(options =>
{
    options.Limits.MaxRequestBodySize = 10 * 1024 * 1024; 
});

var app = builder.Build();

app.UseCors("AllowAll");

app.UseRouting();

app.MapControllers();

Console.WriteLine("=== Image Classifier API Backend is starting on http://localhost:5100 ===");
app.Run("http://localhost:5100");
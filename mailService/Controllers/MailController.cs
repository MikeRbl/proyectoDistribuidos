using Microsoft.AspNetCore.Mvc;
using mail_service.Models;
using System.Text;
using System.Text.Json;

namespace mail_service.Controllers
{
    [Route("api/Pipedream")]
    [ApiController]
    public class MailController : ControllerBase
    {
        private readonly HttpClient _httpClient;

        public MailController()
        {
            _httpClient = new HttpClient();
        }

        // Endpoint: POST api/Pipedream/welcome
        [HttpPost("welcome")]
        public async Task<IActionResult> SendWelcomeEmail([FromBody] WelcomeRequest request)
        {
            // 1. URL de tu Trigger de Pipedream
            string pipedreamUrl = "https://eo5jow2v6ggrz57.m.pipedream.net";

            var jsonOptions = new JsonSerializerOptions
            {
                PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
                WriteIndented = true
            };

            // 2. Serializar el objeto (nombre, email) a JSON
            var jsonContent = new StringContent(
                JsonSerializer.Serialize(request, jsonOptions),
                Encoding.UTF8,
                "application/json"          
            );

            try 
            {
                // 3. Enviar la petición POST real a Pipedream
                var response = await _httpClient.PostAsync(pipedreamUrl, jsonContent);

                if (response.IsSuccessStatusCode)
                {
                    Console.WriteLine($"[Exito] Correo enviado a Pipedream para: {request.Email}");
                    return Ok(new { message = "Correo enviado exitosamente" });
                }
                else 
                {
                    Console.WriteLine($"[Error] Pipedream respondió: {response.StatusCode}");
                    return StatusCode((int)response.StatusCode, new { message = "Error al contactar Pipedream" });
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"[Exception] {ex.Message}");
                return StatusCode(500, new { message = "Error interno enviando correo" });
            }
        }

        // Endpoint: POST api/Pipedream/verification-code
        [HttpPost("verification-code")]
        public async Task<IActionResult> SendVerificationCode([FromBody] WelcomeRequest request)
        {
            // URL DE TU NUEVO TRIGGER PIPEDREAM (Recuperación)
            string pipedreamUrlV = "https://eoejnjf99o83jns.m.pipedream.net";

            var jsonOptions = new JsonSerializerOptions
            {
                PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
                WriteIndented = true
            };

            var jsonContent = new StringContent(
                JsonSerializer.Serialize(request, jsonOptions),
                Encoding.UTF8,
                "application/json"
            );

            try 
            {
                var response = await _httpClient.PostAsync(pipedreamUrlV, jsonContent);

                if (response.IsSuccessStatusCode)
                {
                    Console.WriteLine($"[Exito] Código de recuperación solicitado para: {request.Email}");
                    return Ok(new { message = "Código de verificación enviado" });
                }
                else 
                {
                    Console.WriteLine($"[Error] Pipedream respondió: {response.StatusCode}");
                    return StatusCode((int)response.StatusCode, new { message = "Error al contactar Pipedream" });
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"[Exception] {ex.Message}");
                return StatusCode(500, new { message = "Error interno enviando código" });
            }
        }

        // Endpoint: POST api/Pipedream/verify-code
        [HttpPost("verify-code")]
        public async Task<IActionResult> VerifyCode([FromBody] WelcomeRequest request)
        {
            // URL de tu SEGUNDO trigger (Verificación)
            string pipedreamUrl = "https://eopmerur2pv18d2.m.pipedream.net";

            // Reutilizamos el modelo WelcomeRequest porque tiene Email y (Code/Nombre)
            // Para simplificar, enviaremos el 'code' en el campo que se llame 'code' en el JSON
            // Nota: Debemos crear un objeto anónimo para enviar 'code' explícitamente si tu modelo no lo tiene claro.
            
            var payload = new { 
                email = request.Email,
                code = request.Code // Asegúrate de agregar la propiedad 'Code' a tu modelo o usar 'Nombre' temporalmente
            };

            var jsonContent = new StringContent(
                JsonSerializer.Serialize(payload),
                Encoding.UTF8,
                "application/json"
            );

            try 
            {
                var response = await _httpClient.PostAsync(pipedreamUrl, jsonContent);

                if (response.IsSuccessStatusCode)
                {
                    return Ok(new { message = "Código verificado" });
                }
                else 
                {
                    // Si Pipedream responde 400 (Código inválido), nosotros también
                    return BadRequest(new { message = "Código incorrecto" });
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine(ex.Message);
                return StatusCode(500, new { message = "Error de conexión" });
            }
        }
    }

}
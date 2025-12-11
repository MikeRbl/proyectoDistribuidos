namespace mail_service.Models
{
    public class WelcomeRequest
    {
        public string? Email { get; set; }
        public string? Nombre { get; set; }
        public string? Code { get; set; }
    }
    
    // Agregamos de una vez el modelo para recuperación de contraseña
    public class RecoveryRequest
    {
        public string? Email { get; set; }
        public string? Code { get; set; }
    }
}
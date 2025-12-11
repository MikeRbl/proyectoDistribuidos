// Define la estructura de datos de un Usuario basada en la tabla MySQL
export interface User {
    id?: string; // Es opcional al crear porque lo generamos nosotros o la BD
    nombre: string;
    apellidos: string;
    email: string;
    password?: string; // Opcional porque a veces no queremos enviarlo al front
    telefono?: string;
    fecha_registro?: Date;
    fecha_ultima_sesion?: Date;
    activo?: boolean;
}
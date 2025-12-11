import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';
import authRoutes from './routes/authRoutes';
import pool from './config/db';

dotenv.config();

const app = express();
const port = process.env.PORT || 3000;

app.use(express.json());
app.use(cors());

// --- RUTAS ---
// Todas las rutas de auth empezarán con /api/auth
// Ejemplo final: http://localhost:3000/api/auth/register
app.use('/api/auth', authRoutes);


// Endpoint de prueba para verificar la BD
app.get('/test-db', async (req, res) => {
    res.json({ message: "API funcionando" });
    try {
        // Intentamos obtener una conexión del pool
        const connection = await pool.getConnection();
        
        // Hacemos una consulta simple (ej. ver la versión de MySQL)
        const [rows] = await connection.query('SELECT VERSION() as version');
        
        // Liberamos la conexión
        connection.release();
        
        res.json({ 
            status: 'Conexión exitosa a MySQL', 
            version: rows 
        });

    } catch (error) {
        console.error(error);
        res.status(500).json({ status: 'Error al conectar a la BD', error });
    }
});

app.listen(port, () => {
    console.log(`[server]: Servidor corriendo en http://localhost:${port}`);
});
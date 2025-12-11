import { Router } from 'express';
import { AuthController } from '../controllers/AuthController';

const router = Router();

// Definimos el endpoint POST /register
router.post('/register', AuthController.register);
router.post('/login', AuthController.login);
router.post('/forgot-password', AuthController.forgotPassword);
router.post('/verify-code', AuthController.verifyCode);

export default router;
import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../service/auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Token guardado al hacer login (null si no hay sesión)
  const token = inject(AuthService).getToken();

  // Siempre: que Laravel responda en JSON, también los errores (422, 401, 429...)
  const cabeceras: Record<string, string> = {
    Accept: 'application/json',
  };

  // El token solo si hay sesión; si no, mandaríamos "Bearer null"
  if (token) {
    cabeceras['Authorization'] = `Bearer ${token}`;
  }

  // req no se puede modificar: clone() crea una copia con las cabeceras añadidas
  const reqConCabeceras = req.clone({ setHeaders: cabeceras });

  // Enviamos la copia, no la original
  return next(reqConCabeceras);
};

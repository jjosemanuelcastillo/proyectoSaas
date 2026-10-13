import { HttpClient } from '@angular/common/http';
import { computed, inject, Injectable, signal } from '@angular/core';
import { environment } from '../../../environments/environment';
import {
  AuthResponse,
  RegistroRequest,
  User,
  LoginRequest,
} from '../models/auth.models';
import { finalize, Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private http = inject(HttpClient);
  usuario = signal<User | null>(null);
  estaLogueado = computed(() => this.usuario() !== null);
  esDueno = computed(() => this.usuario()?.rol === 'dueno');

  private guardarSesion(respuesta: AuthResponse): void {
    localStorage.setItem('token', respuesta.token);
    this.usuario.set(respuesta.user);
  }

  registro(datos: RegistroRequest): Observable<AuthResponse> {
    return this.http
      .post<AuthResponse>(`${this.apiUrl}/registro`, datos)
      .pipe(tap((respuesta) => this.guardarSesion(respuesta)));
  }

  login(datos: LoginRequest): Observable<AuthResponse> {
    return this.http
      .post<AuthResponse>(`${this.apiUrl}/login`, datos)
      .pipe(tap((respuesta) => this.guardarSesion(respuesta)));
  }

  //Guarda el token
  getToken(): string | null {
    return localStorage.getItem('token');
  }

  logout(): Observable<unknown> {
    return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
      finalize(() => {
        localStorage.removeItem('token');
        this.usuario.set(null);
      }),
    );
  }
}

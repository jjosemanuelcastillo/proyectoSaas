import { Component, inject, signal } from '@angular/core';
import {
  AbstractControl,
  FormBuilder,
  ReactiveFormsModule,
  ValidationErrors,
  Validators,
} from '@angular/forms';
import { AuthService } from '../../../core/service/auth.service';
import { HttpErrorResponse } from '@angular/common/http';
import { Router } from '@angular/router';
function contrasenaIgual(grupo: AbstractControl): ValidationErrors | null {
  const password = grupo.get('password')?.value;
  const password_Confirmation = grupo.get('password_confirmation')?.value;

  if (password === password_Confirmation) {
    return null;
  }
  return { noCoinciden: true };
}
@Component({
  selector: 'app-registro',
  imports: [ReactiveFormsModule],
  templateUrl: './registro.component.html',
  styleUrl: './registro.component.css',
})
export class RegistroComponent {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private authService = inject(AuthService);
  error = signal<string | null>(null);
  form = this.fb.nonNullable.group(
    {
      name: ['', [Validators.required, Validators.maxLength(255)]],
      email: ['', [Validators.required, Validators.email]],
      telefono: ['', [Validators.required, Validators.pattern(/^\d{9}$/)]],
      password: ['', [Validators.required, Validators.minLength(10)]],
      password_confirmation: ['', [Validators.required]],
    },
    { validators: contrasenaIgual },
  );

  onSubmit() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

     this.error.set(null);

     this.authService.registro(this.form.getRawValue()).subscribe({
      next: (respuesta) => {
        console.log('registrado', respuesta.user);
        this.router.navigate(['/login'])
      },
      error: (err: HttpErrorResponse) => {
        if (err.status === 401) {
          this.error.set(err.error.message); // "Credenciales incorrectas" desde Laravel
        } else if (err.status === 429) {
          this.error.set('Demasiados intentos. Espera un minuto.');
        } else {
          // status 0 = no hay respuesta (Laravel apagado, sin conexión...)
          this.error.set('No se ha podido conectar con el servidor.');
        }
      },
    });
  }
}

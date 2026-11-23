import { Routes } from '@angular/router';
import { LoginComponent } from './features/auth/login/login.component';
import { RegistroComponent } from './features/auth/registro/registro.component';
import { InicioComponent } from './inicio/inicio.component';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  {path:'registro', component: RegistroComponent},
  {path:'login', component: LoginComponent},
  {path:'inicio', component: InicioComponent},
];

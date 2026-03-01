import { Routes } from '@angular/router';
import { RecursoListComponent } from './components/recurso-list/recurso-list.component';
import { RecursoFormComponent } from './components/recurso-form/recurso-form.component';
import { GrupoFormComponent } from './components/grupo-form/grupo-form.component';
import { GrupoDetailComponent } from './components/grupo-detail/grupo-detail.component';

export const routes: Routes = [
  { path: '', component: RecursoListComponent },
  { path: 'novo', component: RecursoFormComponent },
  { path: 'editar/:id', component: RecursoFormComponent },
  { path: 'grupos/novo', component: GrupoFormComponent },
  { path: 'grupos/:id/editar', component: GrupoFormComponent },
  { path: 'grupos/:id', component: GrupoDetailComponent },
  { path: '**', redirectTo: '' }
];

import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Grupo, CreateGrupoRequest, UpdateGrupoRequest } from '../models/grupo.model';

@Injectable({
  providedIn: 'root'
})
export class GrupoService {
  private readonly endpoint = 'v1/grupos';

  constructor(private apiService: ApiService) { }

  listarGrupos(): Observable<Grupo[]> {
    return this.apiService.get<Grupo[]>(this.endpoint);
  }

  buscarGrupoPorId(id: number, withRecursos: boolean = false): Observable<Grupo> {
    const params = withRecursos ? { with_recursos: 'true' } : {};
    return this.apiService.get<Grupo>(`${this.endpoint}/${id}`, params);
  }

  criarGrupo(data: CreateGrupoRequest): Observable<Grupo> {
    return this.apiService.post<Grupo>(this.endpoint, data);
  }

  atualizarGrupo(id: number, data: UpdateGrupoRequest): Observable<Grupo> {
    return this.apiService.put<Grupo>(`${this.endpoint}/${id}`, data);
  }

  excluirGrupo(id: number): Observable<void> {
    return this.apiService.delete<void>(`${this.endpoint}/${id}`);
  }

  syncRecursos(grupoId: number, recursoIds: number[]): Observable<Grupo> {
    return this.apiService.post<Grupo>(`${this.endpoint}/${grupoId}/sync-recursos`, {
      recurso_ids: recursoIds
    });
  }

  gerarDescricao(nome: string): Observable<{ descricao: string }> {
    return this.apiService.post<{ descricao: string }>(`${this.endpoint}/gerar-descricao`, {
      nome
    });
  }
}

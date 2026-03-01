import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Recurso, CreateRecursoRequest, UpdateRecursoRequest } from '../models/recurso.model';
import { PaginatedResponse } from '../models/paginated-response.model';

@Injectable({
  providedIn: 'root'
})
export class RecursoService {
  private readonly endpoint = 'v1/recursos';

  constructor(private apiService: ApiService) { }

  listarRecursos(page: number = 1, perPage: number = 15, search?: string, tipo?: string, tags?: string[]): Observable<PaginatedResponse<Recurso>> {
    let url = `${this.endpoint}?page=${page}&per_page=${perPage}`;
    if (search && search.trim()) {
      url += `&search=${encodeURIComponent(search.trim())}`;
    }
    if (tipo) {
      url += `&tipo=${encodeURIComponent(tipo)}`;
    }
    if (tags && tags.length > 0) {
      url += `&tags=${tags.join(',')}`;
    }
    return this.apiService.get<PaginatedResponse<Recurso>>(url);
  }

  buscarRecursoPorId(id: number): Observable<Recurso> {
    return this.apiService.get<Recurso>(`${this.endpoint}/${id}`);
  }

  criarRecurso(data: CreateRecursoRequest): Observable<Recurso> {
    return this.apiService.post<Recurso>(this.endpoint, data);
  }

  atualizarRecurso(id: number, data: UpdateRecursoRequest): Observable<Recurso> {
    return this.apiService.put<Recurso>(`${this.endpoint}/${id}`, data);
  }

  excluirRecurso(id: number): Observable<{ message: string }> {
    return this.apiService.delete<{ message: string }>(`${this.endpoint}/${id}`);
  }

  gerarDescricao(titulo: string, tipo: string, url?: string | null): Observable<{ descricao: string }> {
    return this.apiService.post<{ descricao: string }>(`${this.endpoint}/gerar-descricao`, {
      titulo,
      tipo,
      url: url || undefined
    });
  }

  gerarTags(titulo: string, tipo: string, descricao?: string | null): Observable<{ tags: string[] }> {
    return this.apiService.post<{ tags: string[] }>(`${this.endpoint}/gerar-tags`, {
      titulo,
      tipo,
      descricao: descricao || undefined
    });
  }
}

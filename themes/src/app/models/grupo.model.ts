import { Recurso } from './recurso.model';

export interface Grupo {
  id: number;
  nome: string;
  descricao?: string | null;
  recursos_count: number;
  recursos?: Recurso[];
  created_at: string;
  updated_at: string;
}

export interface CreateGrupoRequest {
  nome: string;
  descricao?: string | null;
  recurso_ids?: number[];
}

export interface UpdateGrupoRequest {
  nome?: string;
  descricao?: string | null;
  recurso_ids?: number[];
}

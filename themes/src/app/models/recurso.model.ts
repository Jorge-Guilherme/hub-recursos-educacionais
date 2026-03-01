import { Tag } from './tag.model';

export type RecursoTipo = 'video' | 'pdf' | 'link';

export interface Recurso {
  id: number;
  titulo: string;
  descricao: string;
  tipo: RecursoTipo;
  url: string;
  tags: Tag[];
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export interface CreateRecursoRequest {
  titulo: string;
  descricao: string;
  tipo: RecursoTipo;
  url: string;
  tags: string[];
}

export interface UpdateRecursoRequest {
  titulo?: string;
  descricao?: string;
  tipo?: RecursoTipo;
  url?: string;
  tags?: string[];
}

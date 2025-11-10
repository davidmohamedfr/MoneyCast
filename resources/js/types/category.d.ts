export type CategoryType = 'income' | 'expense'

export interface Category {
  id: number
  name: string
  type: CategoryType
  icon: string | null
  created_at: string
  updated_at: string
}

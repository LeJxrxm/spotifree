import type { Artiste } from './Artiste'
import type { Musique } from './Musique'

export type Album = {
    id: number
    titre: string
    coverUrl?: string
    createdAt?: string
    
    // Relations
    artiste?: Artiste
    musiques?: Musique[]

    // UI/View helper fields (optional)
    description?: string
}

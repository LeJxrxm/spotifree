import type { Album } from './Album'
import type { Musique } from './Musique'

export type Artiste = {
    id: number
    nom: string
    imageUrl?: string
    
    // Relations
    albums?: Album[]
    musiques?: Musique[]
    
    // View fields / Virtual fields
    popularTracks?: Musique[]
    listeners?: string
    verified?: boolean
}

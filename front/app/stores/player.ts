import type { Musique } from "~/types/Musique";

export const usePlayerStore = defineStore("player", () => {
  const currentTrack = ref<Musique | null>(null);
  const isPlaying = ref<boolean>(false);
  const volume = ref<number>(1);

  function play(track: Musique): void {
    currentTrack.value = track;
    isPlaying.value = true;
  }

  function pause(): void {
    isPlaying.value = false;
  }

  function togglePlay(): void {
    isPlaying.value = !isPlaying.value;
  }

  return {
    currentTrack,
    isPlaying,
    volume,
    play,
    pause,
    togglePlay,
  };
});

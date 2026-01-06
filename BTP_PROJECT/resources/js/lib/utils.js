import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function valueUpdater(updaterOrValue, ref) {
  ref.value = typeof updaterOrValue === 'function'
    ? updaterOrValue(ref.value)
    : updaterOrValue
}
export function cn(...inputs) {
  return twMerge(clsx(inputs));
}

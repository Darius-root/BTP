// composables/useFlash.js
import { usePage, router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
interface FlashProps { success?: string; error?: string; [key: string]: unknown; }
let initialized = false;
let lastFlash = null;

export function useFlash() {
  const page = usePage();

  if (!initialized) {
    router.on("finish", () => {
      const flash = page.props.flash;
      if (!flash) return;

      // éviter duplication : comparer contenu
      const current = JSON.stringify(flash);
      if (current === lastFlash) return;
      lastFlash = current;
      // @ts-ignore
      if (flash.success) toast.success(flash.success);
      // @ts-ignore
      if (flash.error) toast.error(flash.error);

      // vider après consommation
      page.props.flash = {};
    });

    initialized = true;
  }
}

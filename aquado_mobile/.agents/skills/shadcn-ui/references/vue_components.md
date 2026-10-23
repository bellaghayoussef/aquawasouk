# shadcn-vue Reference Components (Vue 3 / Inertia)

## 1. Dropdown Menu (Accessible Popover)
```vue
<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { cn } from "@/lib/utils";

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggle = () => { isOpen.value = !isOpen.value; };
const close = () => { isOpen.value = false; };

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        close();
    }
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block text-left">
        <div @click="toggle">
            <slot name="trigger" />
        </div>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-50 mt-2 min-w-[8rem] overflow-hidden rounded-xl border border-border bg-popover p-1 text-popover-foreground shadow-md animate-in data-[side=bottom]:slide-in-from-top-2"
            >
                <slot name="content" :close="close" />
            </div>
        </transition>
    </div>
</template>
```

## 2. Table Primitives
```vue
<!-- Table.vue -->
<template>
  <div class="relative w-full overflow-auto">
    <table class="w-full caption-bottom text-sm border-collapse">
      <slot />
    </table>
  </div>
</template>
```

```vue
<!-- TableHeader.vue -->
<template>
  <thead class="[&_tr]:border-b border-border bg-muted/40">
    <slot />
  </thead>
</template>
```

```vue
<!-- TableRow.vue -->
<template>
  <tr class="border-b border-border transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
    <slot />
  </tr>
</template>
```

```vue
<!-- TableHead.vue -->
<template>
  <th class="h-10 px-4 text-left align-middle font-bold text-muted-foreground [&:has([role=checkbox])]:pr-0 text-xs uppercase tracking-wider">
    <slot />
  </th>
</template>
```

```vue
<!-- TableCell.vue -->
<template>
  <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
    <slot />
  </td>
</template>
```

## 3. Tabs Primitives
```vue
<!-- TabsList.vue -->
<template>
  <div class="inline-flex h-10 items-center justify-center rounded-xl bg-muted p-1 text-muted-foreground">
    <slot />
  </div>
</template>
```

```vue
<!-- TabsTrigger.vue -->
<script setup>
import { cn } from "@/lib/utils";

defineProps({
  active: Boolean,
  class: { type: String, default: "" },
});
</script>

<template>
  <button
    type="button"
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 cursor-pointer',
      active ? 'bg-background text-foreground shadow-xs' : 'hover:text-foreground',
      $props.class
    )"
  >
    <slot />
  </button>
</template>
```

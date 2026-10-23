---
name: shadcn-ui
description: "Comprehensive guide, patterns, and component reference for shadcn/ui (React and shadcn-vue for Vue 3). Use this skill when designing, building, configuring, or refactoring UI components using shadcn conventions: Tailwind CSS tokens, CSS variables theme system, cn() utility (clsx + tailwind-merge), accessible primitives (Radix UI / Radix Vue / Reka UI), and copy-paste component architecture for buttons, dialogs, dropdowns, inputs, cards, tables, badges, tabs, and sheets."
---

# shadcn/ui Skill

A complete guide and reference implementation for **shadcn/ui** and **shadcn-vue**.

Unlike traditional component libraries distributed as monolithic npm packages, **shadcn/ui** is a collection of re-usable components that you copy and paste directly into your project. You own the code, the styling, and the behavior.

---

## 1. Core Architecture & Philosophy

1. **You Own the Code**: Components live in your codebase (typically `@/components/ui` or `@/Components/ui`). You are free to modify markup, Tailwind classes, and props directly.
2. **Accessible Primitives**: Built on top of headless, accessible primitives:
   - **React**: `@radix-ui/react-*`
   - **Vue 3**: `radix-vue` (or `reka-ui`)
3. **Tailwind CSS + Class Variance Authority (`cva`)**: Variants (e.g. `variant: "outline"`, `size: "sm"`) are declarative and type-safe.
4. **The `cn()` Helper**: Merges classes safely using `clsx` and `tailwind-merge` to resolve conflicting Tailwind utility classes.
5. **Theme via CSS Variables**: Colors and radius are defined as HSL/OKLCH CSS variables in your global stylesheet, enabling seamless dark mode switching.

---

## 2. Setup & Foundation

### A. The `cn` Class Merger Utility

Create `@/lib/utils.js` (or `.ts`):

```javascript
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
```

### B. CSS Variables Theme (`globals.css` or `app.css`)

```css
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;

    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;

    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;

    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;

    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;

    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;

    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;

    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;

    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;

    --radius: 0.75rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;

    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;

    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;

    --primary: 217.2 91.2% 59.8%;
    --primary-foreground: 222.2 47.4% 11.2%;

    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;

    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;

    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;

    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;

    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 224.3 76.3% 48%;
  }
}
```

### C. `tailwind.config.js` Integration

```javascript
module.exports = {
  darkMode: ["class"],
  theme: {
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
    },
  },
};
```

---

## 3. Essential Component Blueprints

### 1. Button (`Button.vue`)
```vue
<script setup>
import { cva } from "class-variance-authority";
import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap rounded-xl text-sm font-semibold ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 cursor-pointer active:scale-[0.98]",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground shadow hover:bg-primary/90",
        destructive: "bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90",
        outline: "border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground",
        secondary: "bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm: "h-9 rounded-lg px-3 text-xs",
        lg: "h-11 rounded-xl px-8 text-base",
        icon: "h-10 w-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
);

defineProps({
  variant: { type: String, default: "default" },
  size: { type: String, default: "default" },
  as: { type: String, default: "button" },
  class: { type: String, default: "" },
});
</script>

<template>
  <component :is="as" :class="cn(buttonVariants({ variant, size }), $props.class)">
    <slot />
  </component>
</template>
```

### 2. Card Components (`Card.vue`, `CardHeader.vue`, etc.)
```vue
<!-- Card.vue -->
<script setup>
import { cn } from "@/lib/utils";
defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <div :class="cn('rounded-2xl border bg-card text-card-foreground shadow-sm', $props.class)">
    <slot />
  </div>
</template>
```

```vue
<!-- CardHeader.vue -->
<script setup>
import { cn } from "@/lib/utils";
defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <div :class="cn('flex flex-col space-y-1.5 p-6', $props.class)">
    <slot />
  </div>
</template>
```

```vue
<!-- CardTitle.vue -->
<script setup>
import { cn } from "@/lib/utils";
defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <h3 :class="cn('font-semibold leading-none tracking-tight text-lg', $props.class)">
    <slot />
  </h3>
</template>
```

```vue
<!-- CardContent.vue -->
<script setup>
import { cn } from "@/lib/utils";
defineProps({ class: { type: String, default: "" } });
</script>

<template>
  <div :class="cn('p-6 pt-0', $props.class)">
    <slot />
  </div>
</template>
```

### 3. Input (`Input.vue`)
```vue
<script setup>
import { cn } from "@/lib/utils";

defineProps({
  modelValue: [String, Number],
  class: { type: String, default: "" },
});
defineEmits(["update:modelValue"]);
</script>

<template>
  <input
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    :class="cn(
      'flex h-10 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-colors',
      $props.class
    )"
  />
</template>
```

### 4. Badge (`Badge.vue`)
```vue
<script setup>
import { cva } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
  {
    variants: {
      variant: {
        default: "border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80",
        secondary: "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
        destructive: "border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80",
        outline: "text-foreground",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

defineProps({
  variant: { type: String, default: "default" },
  class: { type: String, default: "" },
});
</script>

<template>
  <div :class="cn(badgeVariants({ variant }), $props.class)">
    <slot />
  </div>
</template>
```

### 5. Dialog / Modal Anatomy (Radix / Reka Primitive)
- **DialogRoot**: Controls open/close state.
- **DialogTrigger**: Accessible button activating the dialog.
- **DialogPortal**: Teleports dialog content to `<body>`.
- **DialogOverlay**: Fixed backdrop (`fixed inset-0 z-50 bg-black/60 backdrop-blur-xs`).
- **DialogContent**: Centered modal box (`fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-2xl border bg-background p-6 shadow-xl`).
- **DialogClose**: Close icon button in the top-right corner.

---

## 4. Best Practices & Rules

1. **Accessibility First**:
   - Ensure interactive elements support keyboard navigation (`Tab`, `Space`, `Enter`, `Escape`).
   - Use `aria-describedby` and `aria-labelledby` for dialogs and tooltips.
2. **Always Use `cn()`**:
   - Never concatenate class strings manually with template literals without `twMerge`.
   - `cn('p-4', props.class)` ensures caller classes like `p-6` take precedence properly.
3. **Responsive & Mobile First**:
   - Drawers and Sheets should slide in from bottom or right on mobile screens.
   - Minimum tap target size: 44x44px.
4. **Dark Mode Cohesion**:
   - Use semantic tokens (`bg-background text-foreground`, `bg-card text-card-foreground`, `border-border`) rather than hardcoded `bg-white dark:bg-gray-900`.
5. **No Raw Emojis**:
   - Always pair shadcn components with Lucide or Heroicon SVGs.

---

## 5. References & Guides
- [Vue 3 Components Guide](./references/vue_components.md)
- [Tailwind Theme Reference](./references/tailwind_theme.css)

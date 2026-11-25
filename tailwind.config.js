/** @type {import('tailwindcss').Config} */
export default {
  future: {
    hoverOnlyWhenSupported: true,
  },
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        canvas: {
          DEFAULT: "#f2f7fd",
          muted: "#e6eff9",
          subtle: "#cbdced",
        },
        surface: {
          DEFAULT: "#ffffff",
          elevated: "#ffffff",
          inset: "#f3f8fe",
        },
        ink: {
          DEFAULT: "#172b4d",
          secondary: "#354c68",
          muted: "#52657d",
          faint: "#61758f",
        },
        charcoal: {
          DEFAULT: "#17385f",
          deep: "#172b4d",
          soft: "#eaf2fc",
        },
        primary: {
          DEFAULT: "#1264c7",
          hover: "#0b53aa",
          soft: "#e8f2ff",
        },
        bronze: {
          DEFAULT: "#1264c7",
          soft: "#e8f2ff",
          border: "#a9c9ed",
        },
        crimson: {
          DEFAULT: "#9F1239",
          soft: "#FDF2F4",
        },
        border: {
          DEFAULT: "#dfe9f5",
          strong: "#91a8c3",
        },
      },
      fontFamily: {
        sans: ['"Onest"', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      transitionTimingFunction: {
        'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
        'out-ui': 'cubic-bezier(0.2, 0, 0, 1)',
      },
    },
  },
  plugins: [],
};

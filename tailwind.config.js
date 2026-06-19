module.exports = {
  content: [
    './src/**/*.{ts,tsx,js,jsx}',
    './assets/**/*.{ts,tsx,js,jsx,css}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Stitch design tokens
        primary: {
          DEFAULT: '#3525cd', // Primary
          container: '#4f46e5', // Primary container
          foreground: '#ffffff',
        },
        secondary: {
          DEFAULT: '#505f76', // Secondary
          container: '#d0e1fb', // Secondary container
          foreground: '#ffffff',
          'on-secondary-container': '#54647a', // On secondary container
        },
        tertiary: {
          DEFAULT: '#7e3000', // Tertiary
          container: '#a44100', // Tertiary container
          foreground: '#ffffff',
          'on-tertiary-container': '#ffd2be', // On tertiary container
        },
        background: '#fcf8ff', // Background
        surface: '#ffffff', // Surface
        'surface-container': '#f0ecf9', // Surface container
        'surface-container-high': '#eae6f4', // Surface container high
        'surface-container-highest': '#e4e1ee', // Surface container highest
        'surface-container-low': '#f5f2ff', // Surface container low
        'surface-container-lowest': '#ffffff', // Surface container lowest
        'surface-tint': '#4d44e3', // Surface tint
        'surface-variant': '#e4e1ee', // Surface variant
        'on-background': '#1b1b24', // On background
        'on-surface': '#1b1b24', // On surface
        'on-surface-variant': '#464555', // On surface variant
        outline: '#777587',
        'outline-variant': '#c7c4d8',
        error: {
          DEFAULT: '#ba1a1a',
          container: '#ffdad6',
          'on-error': '#ffffff',
          'on-error-container': '#93000a',
        },
        success: {
          DEFAULT: '#047857',
          container: '#d1fae5',
          'on-success': '#ffffff',
          'on-success-container': '#065f46',
        },
        warning: {
          DEFAULT: '#b45309',
          container: '#fef3c7',
          'on-warning': '#ffffff',
          'on-warning-container': '#92400e',
        },
        // Neutral colors
        neutral: {
          DEFAULT: '#1b1b24',
          variant1: '#464555',
          variant2: '#777587',
          variant3: '#c7c4d8',
          variant4: '#e4e1ee',
          variant5: '#f0ecf9',
          variant6: '#f5f2ff',
          variant7: '#ffffff',
        },
      },
      borderRadius: {
        none: '0',
        sm: '0.25rem',
        DEFAULT: '0.5rem',
        md: '0.75rem',
        lg: '1rem',
        xl: '1.5rem',
        '2xl': '2rem',
        '3xl': '3rem',
        full: '9999px',
      },
      spacing: {
        '0': '0',
        'xs': '0.25rem',
        'sm': '0.5rem',
        'md': '1rem',
        'lg': '1.5rem',
        'xl': '2rem',
        '2xl': '3rem',
        '3xl': '4rem',
        '4xl': '6rem',
        '5xl': '8rem',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        mono: ['ui-monospace', 'monospace'],
      },
      fontSize: {
        xs: ['0.75rem', { lineHeight: '1rem', fontWeight: '400' }],
        sm: ['0.875rem', { lineHeight: '1.25rem', fontWeight: '400' }],
        base: ['1rem', { lineHeight: '1.5rem', fontWeight: '400' }],
        lg: ['1.125rem', { lineHeight: '1.75rem', fontWeight: '400' }],
        xl: ['1.25rem', { lineHeight: '1.75rem', fontWeight: '600' }],
        '2xl': ['1.5rem', { lineHeight: '2rem', fontWeight: '600' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem', fontWeight: '600' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem', fontWeight: '600' }],
        '5xl': ['3rem', { lineHeight: '1', fontWeight: '600' }],
      },
      fontWeight: {
        thin: '100',
        extralight: '200',
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
        extrabold: '800',
        black: '900',
      },
    },
  },
};

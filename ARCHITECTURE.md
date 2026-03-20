# Architecture: polyfill-intl-icu

## Purpose

Provides a pure-PHP fallback for ICU-based `intl` extension classes including `Collator`,
`NumberFormatter`, `Locale`, and others. Enables locale-aware number/string formatting
on systems without `intl` or with an outdated ICU version.

## Directory Structure

```
Icu/         # Implementation classes mirroring intl extension class hierarchy
bootstrap.php  # Conditionally loads polyfill classes if intl is absent or too old
```

## Key Design Decisions

Classes are defined only when the native `intl` extension is missing or below a required
ICU version. Composer's `extra.symfony.require` version constraint ensures this polyfill
is only required when actually needed.

## Extension Points

None — drop-in class polyfill. Use the standard `intl` extension API.

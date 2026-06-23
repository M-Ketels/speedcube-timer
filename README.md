# cube-timer

A minimalist Rubik's Cube timer application inspired by the clean aesthetics of Monkeytype. Built as a self-hosted platform to run locally on a home network via a mini-PC homelab.

## Project Goals
*   **Minimalist Aesthetic:** A distraction-free, dark-mode-first interface focusing heavily on typography and clean spacing.
*   **WCA Scramble:** Integration of official scramble generators using competition-accurate JavaScript tooling.
*   **User-Scoped Analytics:** Track personal bests (PBs), Average of 5 (Ao5), Average of 12 (Ao12), and complete solve histories bound directly to an authenticated account.
*   **Self-Hosted:** Optimized to run inside a local home network environment with lightweight dependencies.

## Tech Stack
*   **Backend:** Laravel 13 (PHP 8.4)
*   **Frontend:** Vue 3 + Tailwind CSS (via Inertia.js for modern SPA reactivity without API overhead)
*   **Authentication:** Laravel Breeze (Built-in, self-hosted session auth)
*   **Database:** SQLite (Local development)
*   **Testing:** Pest PHP Framework


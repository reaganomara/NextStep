<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NextStep is an AI-powered app in development to help people experiencing health anxiety figure out their next step. Coming soon.">
    <meta name="theme-color" content="#1f5f8b">
    <title>NextStep | A calmer way to figure out your next step</title>
    <style>
        :root {
            color-scheme: light;

            /* Core brand */
            --blue-900: #12344d;
            --blue-700: #1f5f8b;
            --blue-500: #3b7fb0;
            --blue-100: #e3eff7;
            --blue-50: #f1f7fb;
            --teal-700: #17695f;
            --teal-500: #2a9d8f;
            --teal-100: #dff3f0;

            /* Calming accents */
            --sage-700: #3f6650;
            --sage-400: #9dbfa8;
            --sage-100: #e9f2ec;
            --yellow-700: #6e5310;
            --yellow-300: #f4dc8e;
            --yellow-100: #fdf6dc;

            /* Neutrals */
            --neutral-900: #1f2a33;
            --neutral-700: #46545f;
            --neutral-500: #6b7882;
            --neutral-300: #d5dde3;
            --neutral-100: #f4f7f9;
            --page: #f7faf9;
            --white: #ffffff;

            /* Emergency */
            --alert-800: #8a2222;
            --alert-700: #a12b2b;
            --alert-100: #fdecec;

            /* Crisis support (guided examples) */
            --violet-700: #5a3d8a;
            --violet-100: #f2edf9;

            --radius-lg: 20px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-card: 0 1px 2px rgba(18, 52, 77, 0.04), 0 8px 24px rgba(18, 52, 77, 0.07);
            --focus-ring: 0 0 0 3px var(--white), 0 0 0 6px var(--blue-700);
            --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 1rem;
            -webkit-text-size-adjust: 100%;
            overflow-x: hidden;
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }

        body {
            margin: 0;
            font-family: var(--font-sans);
            font-size: 1.0625rem;
            line-height: 1.7;
            color: var(--neutral-900);
            background: var(--page);
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            overflow-x: clip;
        }

        .container {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        a {
            color: var(--blue-700);
            text-underline-offset: 0.18em;
            text-decoration-thickness: 1px;
        }

        a:hover { text-decoration-thickness: 2px; }

        :focus-visible {
            outline: 3px solid var(--blue-700);
            outline-offset: 3px;
            border-radius: 4px;
        }

        h1, h2, h3 {
            line-height: 1.2;
            color: var(--blue-900);
            letter-spacing: -0.015em;
            text-wrap: balance;
        }

        h2 { font-size: clamp(1.5rem, 3.2vw, 1.85rem); margin: 0 0 0.85rem; }

        h3 { font-size: 1.15rem; margin: 0 0 0.4rem; }

        p { margin: 0 0 1rem; max-width: 68ch; }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .skip-link {
            position: absolute;
            left: 1rem;
            top: -100px;
            z-index: 100;
            background: var(--white);
            color: var(--blue-900);
            font-weight: 700;
            padding: 0.6rem 1rem;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-card);
        }

        .skip-link:focus { top: 1rem; }

        /* Emergency contact strip, always first on the page */
        .crisis-bar {
            background: var(--alert-800);
            color: var(--white);
            font-size: 0.92rem;
            line-height: 1.45;
        }

        .crisis-bar .container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.35rem 1.25rem;
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
            text-align: center;
        }

        .crisis-bar strong { font-weight: 700; }

        .crisis-bar a {
            color: var(--white);
            font-weight: 700;
            text-decoration: underline;
        }

        .crisis-bar a:focus-visible { outline-color: var(--white); }

        /* Hero */
        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background: linear-gradient(135deg, var(--blue-700) 0%, #23789a 48%, var(--teal-500) 100%);
            color: var(--white);
            text-align: center;
            padding: 4.5rem 0 6.5rem;
        }

        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            z-index: -1;
            border-radius: 50%;
            pointer-events: none;
        }

        .hero::before {
            width: 520px;
            height: 520px;
            top: -220px;
            left: -160px;
            background: radial-gradient(circle at center, rgba(157, 191, 168, 0.55), rgba(157, 191, 168, 0) 70%);
        }

        .hero::after {
            width: 460px;
            height: 460px;
            right: -140px;
            bottom: -160px;
            background: radial-gradient(circle at center, rgba(244, 220, 142, 0.45), rgba(244, 220, 142, 0) 70%);
        }

        .hero-ring {
            position: absolute;
            z-index: -1;
            right: 8%;
            top: 14%;
            width: 180px;
            height: 180px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-ring.small {
            right: auto;
            left: 10%;
            top: auto;
            bottom: 26%;
            width: 90px;
            height: 90px;
        }

        .hero h1 {
            color: var(--white);
            font-size: clamp(2.75rem, 9vw, 4.5rem);
            font-weight: 800;
            margin: 0 0 0.5rem;
            letter-spacing: -0.035em;
        }

        .hero .subtitle {
            font-size: clamp(1.15rem, 3.5vw, 1.55rem);
            font-weight: 500;
            margin: 0 auto 1.5rem;
            max-width: 32ch;
        }

        .hero .intro {
            max-width: 600px;
            margin: 0 auto 1rem;
            color: rgba(255, 255, 255, 0.96);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: var(--white);
            color: var(--blue-900);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.55rem 1.35rem;
            border-radius: 999px;
            margin-bottom: 1.75rem;
            box-shadow: 0 6px 20px rgba(18, 52, 77, 0.18);
        }

        .badge::before {
            content: "";
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 50%;
            background: var(--sage-400);
            box-shadow: 0 0 0 3px var(--sage-100);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            font: inherit;
            font-weight: 600;
            font-size: 1rem;
            line-height: 1.3;
            text-decoration: none;
            border-radius: 999px;
            padding: 0.65rem 1.35rem;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .btn-light { background: var(--white); color: var(--blue-900); border: 2px solid var(--white); }

        .btn-light:hover { background: var(--yellow-100); border-color: var(--yellow-100); }

        .btn-ghost { background: transparent; color: var(--white); border: 2px solid rgba(255, 255, 255, 0.75); }

        .btn-ghost:hover { background: rgba(255, 255, 255, 0.14); }

        .hero a:focus-visible { outline-color: var(--white); }

        .hero-wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            width: 100%;
            height: 64px;
            display: block;
        }

        /* Sections and decorative background shapes */
        main { position: relative; }

        section {
            position: relative;
            padding: 2.25rem 0;
        }

        .decor {
            position: absolute;
            z-index: 0;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(2px);
        }

        .decor-sage { background: radial-gradient(circle, rgba(157, 191, 168, 0.42), rgba(157, 191, 168, 0) 68%); }

        .decor-yellow { background: radial-gradient(circle, rgba(244, 220, 142, 0.5), rgba(244, 220, 142, 0) 68%); }

        .decor-teal { background: radial-gradient(circle, rgba(42, 157, 143, 0.2), rgba(42, 157, 143, 0) 68%); }

        .decor-blue { background: radial-gradient(circle, rgba(59, 127, 176, 0.18), rgba(59, 127, 176, 0) 68%); }

        section > .container { position: relative; z-index: 1; }

        .eyebrow {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--teal-700);
            margin-bottom: 0.5rem;
        }

        .card {
            position: relative;
            background: var(--white);
            border: 1px solid rgba(213, 221, 227, 0.9);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-card);
        }

        .card-grid {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 860px) {
            .card-grid { grid-template-columns: 1fr 1fr; }
        }

        .accent-top { overflow: hidden; }

        .accent-top::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
        }

        .accent-teal::before { background: linear-gradient(90deg, var(--teal-500), var(--sage-400)); }

        .accent-blue::before { background: linear-gradient(90deg, var(--blue-700), var(--teal-500)); }

        .planned {
            display: inline-block;
            background: var(--yellow-100);
            color: var(--yellow-700);
            border: 1px solid var(--yellow-300);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.2rem 0.75rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            display: grid;
            gap: 0.6rem;
        }

        .feature-list li {
            position: relative;
            background: var(--sage-100);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem 0.75rem 2.6rem;
            line-height: 1.5;
        }

        .feature-list li::before {
            content: "";
            position: absolute;
            left: 1rem;
            top: 1.05rem;
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 50%;
            background: var(--teal-500);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.2);
        }

        .disclaimer {
            background: linear-gradient(135deg, var(--blue-50), var(--white) 70%);
            border-left: 6px solid var(--blue-700);
        }

        .disclaimer p { margin: 0; font-weight: 500; }

        .emergency {
            border: 2px solid var(--alert-700);
            background: var(--alert-100);
            box-shadow: 0 8px 24px rgba(161, 43, 43, 0.1);
        }

        .emergency h2 { color: var(--alert-700); }

        .emergency ul {
            list-style: none;
            padding: 0;
            margin: 0 0 1.25rem;
            display: grid;
            gap: 0.6rem;
        }

        .emergency li {
            background: var(--white);
            border: 1px solid rgba(161, 43, 43, 0.25);
            border-radius: var(--radius-md);
            padding: 0.8rem 1rem;
            line-height: 1.55;
        }

        .emergency a { color: var(--alert-700); font-weight: 700; }

        .emergency a:focus-visible { outline-color: var(--alert-700); }

        .note { font-size: 0.95rem; color: var(--neutral-700); margin: 0; }

        /* Demonstration */
        .demo-label {
            background: var(--blue-900);
            color: var(--white);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.45rem 1rem;
            border-radius: 999px;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .demo-intro {
            display: grid;
            gap: 0;
        }

        .demo-callout {
            background: var(--yellow-100);
            border: 1px solid var(--yellow-300);
            border-radius: var(--radius-md);
            padding: 0.9rem 1.1rem;
            margin-bottom: 1rem;
            max-width: none;
        }

        .scenarios {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .scenario {
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            background: var(--neutral-100);
        }

        .scenario p { margin-bottom: 0.75rem; }

        .scenario code {
            display: inline-block;
            font-size: 0.82rem;
            color: var(--neutral-700);
            background: var(--white);
            border: 1px solid var(--neutral-300);
            border-radius: 6px;
            padding: 0.1rem 0.5rem;
            word-break: break-word;
        }

        button.run {
            font: inherit;
            font-weight: 600;
            min-height: 44px;
            color: var(--white);
            background: var(--blue-700);
            border: 0;
            border-radius: 999px;
            padding: 0.6rem 1.3rem;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        button.run:hover, button.run:focus-visible { background: var(--blue-900); }

        button.run:disabled { opacity: 0.6; cursor: wait; }

        .result { margin-top: 1rem; }

        .result:empty { margin-top: 0; }

        .result-box {
            border-radius: var(--radius-md);
            padding: 0.9rem 1rem;
            margin-bottom: 0.75rem;
        }

        .result-box p:last-child { margin-bottom: 0; }

        .result-label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
        }

        .support { background: var(--teal-100); border: 1px solid var(--teal-500); }

        .support .result-label { color: var(--teal-700); }

        .override { background: var(--alert-100); border: 2px solid var(--alert-700); }

        .override .result-label { color: var(--alert-700); }

        .withheld { background: var(--white); border: 1px dashed var(--neutral-700); color: var(--neutral-700); }

        .withheld .text { text-decoration: line-through; }

        .withheld-note { background: var(--white); border: 1px solid var(--neutral-300); font-size: 0.92rem; }

        .withheld-note .result-label { color: var(--neutral-700); }

        button.toggle {
            font: inherit;
            font-size: 0.92rem;
            font-weight: 600;
            min-height: 44px;
            color: var(--blue-700);
            background: var(--white);
            border: 1px solid var(--blue-700);
            border-radius: 999px;
            padding: 0.45rem 1rem;
            cursor: pointer;
        }

        button.toggle:hover, button.toggle:focus-visible { background: var(--blue-100); }

        .withheld[hidden] { display: none; }

        .withheld:not([hidden]) { margin-top: 0.75rem; margin-bottom: 0; }

        .logged { background: var(--white); border: 1px solid var(--neutral-300); font-size: 0.92rem; }

        .logged code { word-break: break-word; }

        /* Guided example walkthrough */
        .stepper {
            list-style: none;
            padding: 0;
            margin: 1.25rem 0 1.75rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }

        .stepper li {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.3;
            color: var(--neutral-500);
        }

        .stepper li::after {
            content: "";
            position: absolute;
            top: 1rem;
            left: calc(50% + 1.4rem);
            right: calc(-50% + 1.4rem);
            height: 2px;
            background: var(--neutral-300);
        }

        .stepper li:last-child::after { display: none; }

        .step-dot {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--neutral-100);
            border: 2px solid var(--neutral-300);
            color: var(--neutral-700);
            font-weight: 700;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .stepper li.is-current { color: var(--blue-900); }

        .stepper li.is-current .step-dot {
            background: var(--blue-700);
            border-color: var(--blue-700);
            color: var(--white);
            box-shadow: 0 0 0 4px var(--blue-100);
        }

        .stepper li.is-done { color: var(--teal-700); }

        .stepper li.is-done .step-dot { background: var(--teal-100); border-color: var(--teal-500); color: var(--teal-700); }

        .stepper li.is-done::after { background: var(--teal-500); }

        .guided-panel h3 { font-size: 1.2rem; margin-bottom: 0.75rem; }

        .guided-panel h3:focus:not(:focus-visible) { outline: none; }

        .person-grid {
            display: grid;
            gap: 0.85rem;
        }

        @media (min-width: 700px) {
            .person-grid { grid-template-columns: 1fr 1fr; }
        }

        button.person {
            font: inherit;
            text-align: left;
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            color: var(--neutral-900);
            background: var(--white);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            padding: 1rem 1.1rem;
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }

        button.person:hover,
        button.person:focus-visible {
            border-color: var(--teal-500);
            box-shadow: var(--shadow-card);
            transform: translateY(-1px);
        }

        button.person:disabled { opacity: 0.6; cursor: wait; }

        .avatar {
            flex: none;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--blue-900);
            background: linear-gradient(135deg, var(--sage-100), var(--blue-100));
            box-shadow: 0 0 0 1px var(--neutral-300);
        }

        .person-name {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--blue-900);
            margin-bottom: 0.2rem;
        }

        .fictional {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: var(--yellow-100);
            color: var(--yellow-700);
            border: 1px solid var(--yellow-300);
            border-radius: 999px;
            padding: 0.05rem 0.55rem;
        }

        .person-story { display: block; font-size: 0.95rem; line-height: 1.55; color: var(--neutral-700); }

        .person-cta { display: block; margin-top: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--blue-700); }

        .guided-story {
            background: var(--neutral-100);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            padding: 0.9rem 1.1rem;
            max-width: none;
        }

        .check-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1rem;
            display: grid;
            gap: 0.6rem;
        }

        .check-list li {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 0.75rem;
            background: var(--white);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            line-height: 1.45;
            animation: check-in 0.35s ease both;
        }

        .check-list li:nth-child(2) { animation-delay: 0.3s; }

        .check-list li:nth-child(3) { animation-delay: 0.6s; }

        @keyframes check-in {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: none; }
        }

        .check-q { font-weight: 600; }

        .check-list li.is-skipped .check-q { color: var(--neutral-500); font-weight: 500; }

        .check-result {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 999px;
            padding: 0.15rem 0.75rem;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .check-no { background: var(--sage-100); color: var(--sage-700); }

        .check-yes-urgent { background: var(--alert-100); color: var(--alert-700); border-color: var(--alert-700); }

        .check-yes { background: var(--yellow-100); color: var(--yellow-700); border-color: var(--yellow-300); }

        .check-skipped { background: var(--neutral-100); color: var(--neutral-700); border-color: var(--neutral-300); }

        .check-summary { margin-bottom: 1.25rem; }

        .guided-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        button.link-btn {
            font: inherit;
            font-weight: 600;
            min-height: 44px;
            background: none;
            border: 0;
            color: var(--blue-700);
            text-decoration: underline;
            text-underline-offset: 0.18em;
            padding: 0.5rem 0.25rem;
            cursor: pointer;
        }

        .outcome {
            border: 2px solid;
            border-radius: var(--radius-md);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .outcome-title { font-size: 1.4rem; font-weight: 800; line-height: 1.25; margin: 0 0 0.5rem; }

        .outcome p:last-child { margin-bottom: 0; }

        .outcome-emergency { background: var(--alert-100); border-color: var(--alert-700); }

        .outcome-emergency .outcome-title, .outcome-emergency .result-label { color: var(--alert-700); }

        .outcome-crisis { background: var(--violet-100); border-color: var(--violet-700); }

        .outcome-crisis .outcome-title, .outcome-crisis .result-label { color: var(--violet-700); }

        .outcome-professional { background: var(--yellow-100); border-color: var(--yellow-300); }

        .outcome-professional .outcome-title, .outcome-professional .result-label { color: var(--yellow-700); }

        .outcome-self_care { background: var(--teal-100); border-color: var(--teal-500); }

        .outcome-self_care .outcome-title, .outcome-self_care .result-label { color: var(--teal-700); }

        .outcome-action { margin: 0.25rem 0 0.75rem; }

        .btn-alert { background: var(--alert-700); color: var(--white); border: 2px solid var(--alert-700); }

        .btn-alert:hover { background: var(--alert-800); border-color: var(--alert-800); }

        .btn-violet { background: var(--violet-700); color: var(--white); border: 2px solid var(--violet-700); }

        .btn-violet:hover { background: #47306e; border-color: #47306e; }

        .why h4 { font-size: 1rem; color: var(--blue-900); margin: 0 0 0.25rem; }

        .why .feature-list { margin: 0.5rem 0 1rem; }

        .guided-safety { display: grid; gap: 0.75rem; margin-bottom: 1.25rem; }

        .mini-disclaimer {
            background: var(--blue-50);
            border-left: 4px solid var(--blue-700);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .mini-emergency {
            background: var(--white);
            border: 1px solid rgba(161, 43, 43, 0.35);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .mini-disclaimer p, .mini-emergency p { margin: 0; max-width: none; }

        .mini-emergency a { color: var(--alert-700); font-weight: 700; }

        .guided-error {
            background: var(--alert-100);
            border: 1px solid var(--alert-700);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            margin-top: 1rem;
        }

        .guided-error[hidden] { display: none; }

        /* Mental health awareness */
        .awareness {
            background: linear-gradient(160deg, var(--sage-100) 0%, var(--page) 55%, var(--yellow-100) 100%);
        }

        .awareness-dates {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0 0;
            display: grid;
            gap: 1rem;
        }

        @media (min-width: 760px) {
            .awareness-dates { grid-template-columns: repeat(3, 1fr); }
        }

        .date-card {
            display: flex;
            flex-direction: column;
            background: var(--white);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            padding: 1.25rem;
        }

        .date-card .when {
            display: inline-block;
            align-self: flex-start;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
        }

        .when-sage { background: var(--sage-100); color: var(--sage-700); }

        .when-teal { background: var(--teal-100); color: var(--teal-700); }

        .when-yellow { background: var(--yellow-100); color: var(--yellow-700); }

        .date-card p { font-size: 0.97rem; margin-bottom: 0.75rem; }

        .date-card .source {
            margin-top: auto;
            margin-bottom: 0;
            font-size: 0.88rem;
            color: var(--neutral-700);
        }

        .walks {
            margin-top: 1.5rem;
        }

        .walk-links {
            list-style: none;
            padding: 0;
            margin: 1rem 0 1.25rem;
            display: grid;
            gap: 0.75rem;
        }

        @media (min-width: 760px) {
            .walk-links { grid-template-columns: 1fr 1fr; }
        }

        .walk-links a {
            display: block;
            height: 100%;
            text-decoration: none;
            background: var(--sage-100);
            border: 1px solid var(--sage-400);
            border-radius: var(--radius-md);
            padding: 1rem 1.1rem;
            color: var(--neutral-900);
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .walk-links a:hover { background: var(--white); border-color: var(--teal-500); }

        .walk-links .walk-name {
            display: block;
            font-weight: 700;
            color: var(--blue-700);
            text-decoration: underline;
            text-underline-offset: 0.18em;
        }

        .walk-links .walk-org { display: block; font-size: 0.92rem; color: var(--neutral-700); }

        /* Footer */
        footer {
            text-align: center;
            font-size: 0.92rem;
            color: var(--neutral-700);
            padding: 2.5rem 0 3rem;
            border-top: 1px solid var(--neutral-300);
            background: var(--white);
        }

        footer p { margin: 0 auto 0.5rem; }

        footer .footer-brand { font-weight: 700; color: var(--blue-900); }

        footer a { color: var(--alert-700); font-weight: 700; }

        @media (max-width: 600px) {
            body { font-size: 1rem; }
            .hero { padding: 3.25rem 0 5rem; }
            .hero-ring { display: none; }
            .hero-wave { height: 40px; }
            section { padding: 1.5rem 0; }
            .card { padding: 1.4rem; border-radius: 16px; }
            .hero-actions .btn,
            button.run,
            button.toggle { width: 100%; }
            .stepper li { font-size: 0.78rem; }
            .guided-actions .run,
            .outcome-action .btn { width: 100%; }
            .guided-actions { flex-direction: column-reverse; align-items: stretch; }
        }

        @media (forced-colors: active) {
            .decor, .hero::before, .hero::after, .hero-ring, .hero-wave { display: none; }
            .card, .result-box, .date-card { border: 1px solid CanvasText; }
            button.person, .check-list li, .outcome, .check-result { border: 1px solid CanvasText; }
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main">Skip to main content</a>

    <div class="crisis-bar" role="region" aria-label="Emergency contacts">
        <div class="container">
            <span><strong>Emergency:</strong> call <a href="tel:911">911</a></span>
            <span><strong>Crisis:</strong> call or text <a href="tel:988">988</a></span>
            <span><strong>Poison Control:</strong> <a href="tel:18002221222">1-800-222-1222</a></span>
        </div>
    </div>

    <header class="hero">
        <span class="hero-ring" aria-hidden="true"></span>
        <span class="hero-ring small" aria-hidden="true"></span>
        <div class="container">
            <h1>NextStep</h1>
            <p class="subtitle">A calmer way to figure out your next step.</p>
            <div class="badge">Coming Soon</div>
            <p class="intro">
                NextStep is an AI-powered app being developed to help people experiencing health anxiety
                decide what to do when physical symptoms feel overwhelming.
            </p>
            <p class="intro"><strong>NextStep will not diagnose medical conditions.</strong></p>
            <nav class="hero-actions" aria-label="On this page">
                <a class="btn btn-light" href="#guided-demo">Walk through an example</a>
                <a class="btn btn-ghost" href="#safety-demo">See the safety demonstration</a>
                <a class="btn btn-ghost" href="#emergency">Emergency information</a>
            </nav>
        </div>
        <svg class="hero-wave" viewBox="0 0 1440 64" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <path d="M0 40 C 240 8, 480 8, 720 32 S 1200 64, 1440 24 L 1440 64 L 0 64 Z" fill="#f7faf9"/>
        </svg>
    </header>

    <main id="main" tabindex="-1">
        <section aria-label="About health anxiety and NextStep">
            <span class="decor decor-sage" aria-hidden="true" style="width: 420px; height: 420px; top: -60px; left: -180px;"></span>
            <span class="decor decor-yellow" aria-hidden="true" style="width: 360px; height: 360px; bottom: -120px; right: -140px;"></span>
            <div class="container card-grid">
                <div class="card accent-top accent-teal" id="health-anxiety">
                    <span class="eyebrow">Understanding</span>
                    <h2>What is health anxiety?</h2>
                    <p>
                        Health anxiety is when someone experiences excessive worry about their health or fears
                        that physical symptoms could mean something is seriously wrong.
                    </p>
                    <p>
                        Even something like a headache, a noticeable heartbeat, or an unfamiliar sensation can
                        cause overwhelming worry.
                    </p>
                    <p style="margin-bottom: 0;">
                        People may repeatedly Google symptoms, check their bodies, or seek reassurance, which
                        can sometimes make anxiety worse.
                    </p>
                </div>

                <div class="card accent-top accent-blue" id="how-it-will-help">
                    <span class="planned">Planned features</span>
                    <h2>How NextStep will help</h2>
                    <p>The future app aims to:</p>
                    <ul class="feature-list">
                        <li>Help people find an appropriate next step.</li>
                        <li>Reduce repeated symptom searching and checking.</li>
                        <li>Offer evidence-based anxiety-management strategies.</li>
                        <li>Help users recognize when professional medical attention may be needed.</li>
                        <li>Provide supportive guidance during overwhelming moments.</li>
                    </ul>
                    <p class="note" style="margin-top: 1rem;">
                        These are planned features. NextStep is still in development and does not have
                        working AI features yet.
                    </p>
                </div>
            </div>
        </section>

        <section id="safety-disclaimer">
            <div class="container">
                <div class="card disclaimer">
                    <h2>Safety disclaimer</h2>
                    <p>
                        NextStep is an educational and supportive tool in development. It does not diagnose medical conditions, provide medical advice, or replace doctors, therapists, or other qualified healthcare professionals. Not identifying warning signs does not guarantee that a symptom is harmless.
                    </p>
                </div>
            </div>
        </section>

        <section id="emergency">
            <div class="container">
                <div class="card emergency">
                    <h2>Emergency information</h2>
                    <ul>
                        <li><strong>Medical emergency:</strong> Call <a href="tel:911">911</a> or go to the nearest emergency room.</li>
                        <li><strong>Mental health or suicide-related crisis:</strong> Call or text <a href="tel:988">988</a>.</li>
                        <li><strong>Poisoning or possible medication overdose:</strong> Call Poison Control at <a href="tel:18002221222">1-800-222-1222</a>.</li>
                        <li>If someone is unconscious, having trouble breathing, or experiencing an immediate emergency, call <a href="tel:911">911</a>.</li>
                    </ul>
                    <p class="note">
                        These phone numbers are for the United States. If you are in another country, contact
                        your local emergency services.
                    </p>
                </div>
            </div>
        </section>

        <section id="guided-demo" aria-labelledby="guided-heading">
            <span class="decor decor-blue" aria-hidden="true" style="width: 420px; height: 420px; top: -40px; left: -200px;"></span>
            <div class="container">
                <div class="card accent-top accent-teal">
                    <div class="demo-label">Demonstration only</div>
                    <h2 id="guided-heading">Walk through an example</h2>
                    <p>
                        See how NextStep would choose a safe next step. Pick one of the fictional people below.
                        You will not be asked anything about yourself, nothing you type is collected, and no AI is used.
                    </p>

                    <ol class="stepper" aria-label="Progress">
                        <li class="is-current" data-step="1" aria-current="step"><span class="step-dot" aria-hidden="true">1</span>Choose an example</li>
                        <li data-step="2"><span class="step-dot" aria-hidden="true">2</span>What NextStep checks</li>
                        <li data-step="3"><span class="step-dot" aria-hidden="true">3</span>The next step</li>
                    </ol>

                    <div class="guided-panel" data-panel="1">
                        <h3 tabindex="-1">Step 1: Choose a fictional example</h3>
                        <div class="person-grid">
                            @foreach (config('guided_demo.scenarios') as $id => $guided)
                                <button type="button" class="person" data-url="{{ route('guided-demo.run', $id, false) }}">
                                    <span class="avatar" aria-hidden="true">{{ mb_substr($guided['name'], 0, 1) }}</span>
                                    <span>
                                        <span class="person-name">{{ $guided['name'] }} <span class="fictional">Fictional</span></span>
                                        <span class="person-story">{{ $guided['story'] }}</span>
                                        <span class="person-cta" aria-hidden="true">Choose {{ $guided['name'] }} →</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <p class="guided-error" role="alert" hidden></p>
                    </div>

                    <div class="guided-panel" data-panel="2" hidden>
                        <h3 tabindex="-1">Step 2: What NextStep checks, in order</h3>
                        <p class="guided-story"></p>
                        <p class="note" style="margin-bottom: 1rem;">
                            Emergencies are always checked first. The first “yes” decides the next step.
                        </p>
                        <ol class="check-list"></ol>
                        <p class="check-summary"></p>
                        <div class="guided-actions">
                            <button type="button" class="link-btn" data-action="restart">Choose a different example</button>
                            <button type="button" class="run" data-action="result">See the next step</button>
                        </div>
                    </div>

                    <div class="guided-panel" data-panel="3" hidden>
                        <h3 tabindex="-1">Step 3: The next step</h3>
                        <div class="outcome">
                            <div class="result-label"></div>
                            <p class="outcome-title"></p>
                            <p class="outcome-guidance"></p>
                            <p class="outcome-action" hidden></p>
                        </div>
                        <div class="why">
                            <h4>Why this step</h4>
                            <ul class="feature-list"></ul>
                        </div>
                        <div class="result-box logged guided-logged"></div>
                        <div class="guided-safety">
                            <div class="mini-disclaimer">
                                <p>NextStep is an educational and supportive tool in development. It does not diagnose medical conditions, provide medical advice, or replace doctors, therapists, or other qualified healthcare professionals. Not identifying warning signs does not guarantee that a symptom is harmless.</p>
                            </div>
                            <div class="mini-emergency">
                                <p><strong>In an emergency:</strong> call <a href="tel:911">911</a>. <strong>Crisis:</strong> call or text <a href="tel:988">988</a>. <strong>Poison Control:</strong> <a href="tel:18002221222">1-800-222-1222</a>. These numbers are for the United States.</p>
                            </div>
                        </div>
                        <div class="guided-actions">
                            <button type="button" class="run" data-action="restart">Try another example</button>
                        </div>
                    </div>

                    <p class="sr-only guided-status" aria-live="polite"></p>

                    <noscript>
                        <p class="note" style="margin-top: 1rem;">JavaScript is required to walk through the examples.</p>
                    </noscript>
                </div>
            </div>
        </section>

        <section id="safety-demo">
            <span class="decor decor-teal" aria-hidden="true" style="width: 480px; height: 480px; top: 10%; right: -220px;"></span>
            <span class="decor decor-sage" aria-hidden="true" style="width: 380px; height: 380px; bottom: -80px; left: -200px;"></span>
            <div class="container">
                <div class="card">
                    <div class="demo-label">Demonstration only</div>
                    <h2>Safety override demonstration</h2>
                    <p>
                        This is a demonstration, not a working AI symptom checker. The examples below are fictional and predefined. Nothing you type is collected, and no AI is used.
                    </p>
                    <p>
                        It shows a rule the future app must follow: when an emergency warning sign is present,
                        emergency guidance overrides ordinary anxiety-support guidance, and the override is
                        logged on the server.
                    </p>
                    <p class="demo-callout">
                        <strong>In the real NextStep app, withheld anxiety guidance would never be shown to the user during an emergency.</strong>
                        This demonstration hides it by default and lets you reveal it only so you can see what the override replaced.
                    </p>
                    <p class="note">
                        Each override is logged with only a timestamp, the predefined scenario ID, and the
                        override category. No names, IP addresses, symptoms, or personal health information
                        are recorded, and the log is not publicly available.
                    </p>

                    <div class="scenarios">
                        @foreach (config('safety_demo.scenarios') as $id => $scenario)
                            <div class="scenario">
                                <h3>{{ $scenario['title'] }}</h3>
                                <p>{{ $scenario['description'] }}</p>
                                <p><code>Scenario ID: {{ $id }}</code></p>
                                <button type="button" class="run" data-url="{{ route('safety-demo.run', $id, false) }}">
                                    Run this example
                                </button>
                                <div class="result" aria-live="polite"></div>
                            </div>
                        @endforeach
                    </div>

                    <noscript>
                        <p class="note" style="margin-top: 1rem;">JavaScript is required to run the demonstration.</p>
                    </noscript>
                </div>
            </div>
        </section>

        <section id="awareness" class="awareness" aria-labelledby="awareness-heading">
            <span class="decor decor-yellow" aria-hidden="true" style="width: 400px; height: 400px; top: -100px; right: -160px;"></span>
            <span class="decor decor-blue" aria-hidden="true" style="width: 360px; height: 360px; bottom: -120px; left: -160px;"></span>
            <div class="container">
                <span class="eyebrow">Community</span>
                <h2 id="awareness-heading">Mental health awareness dates</h2>
                <p>
                    These yearly observances are a chance to learn more about mental health and to check in
                    on yourself and the people around you.
                </p>

                <ul class="awareness-dates">
                    <li class="date-card">
                        <span class="when when-sage">Every May</span>
                        <h3>Mental Health Awareness Month</h3>
                        <p>Observed in the United States each May.</p>
                        <p class="source">
                            Source: <a href="https://mhanational.org/mental-health-month/">Mental Health America</a>
                        </p>
                    </li>
                    <li class="date-card">
                        <span class="when when-yellow">September 10</span>
                        <h3>World Suicide Prevention Day</h3>
                        <p>Observed internationally every year on September 10.</p>
                        <p class="source">
                            Source: <a href="https://www.who.int/campaigns/world-suicide-prevention-day">World Health Organization</a>
                        </p>
                    </li>
                    <li class="date-card">
                        <span class="when when-teal">October 10</span>
                        <h3>World Mental Health Day</h3>
                        <p>Observed internationally every year on October 10.</p>
                        <p class="source">
                            Source: <a href="https://www.who.int/campaigns/world-mental-health-day">World Health Organization</a>
                        </p>
                    </li>
                </ul>

                <div class="card walks">
                    <h3>Find a mental health walk</h3>
                    <p>
                        Established organizations list community walks on their own websites. Check their sites
                        for current dates and locations near you.
                    </p>
                    <ul class="walk-links">
                        <li>
                            <a href="https://www.namiwalks.org/">
                                <span class="walk-name">NAMIWalks</span>
                                <span class="walk-org">National Alliance on Mental Illness (NAMI)</span>
                            </a>
                        </li>
                        <li>
                            <a href="https://afsp.org/out-of-the-darkness-walks/">
                                <span class="walk-name">Out of the Darkness Walks</span>
                                <span class="walk-org">American Foundation for Suicide Prevention (AFSP)</span>
                            </a>
                        </li>
                    </ul>
                    <p class="note">
                        NextStep is not affiliated with, endorsed by, or partnered with these organizations, and
                        does not organize, host, or raise funds for any event. Links are provided for information only.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p class="footer-brand">NextStep</p>
            <p>NextStep is in development. Coming soon.</p>
            <p>NextStep does not diagnose medical conditions or provide medical advice.</p>
            <p>
                In an emergency, call <a href="tel:911">911</a>. For a mental health crisis, call or text
                <a href="tel:988">988</a>. Poison Control: <a href="tel:18002221222">1-800-222-1222</a>.
            </p>
        </div>
    </footer>

    <script>
        (function () {
            var token = document.querySelector('meta[name="csrf-token"]').content;

            function box(className, label, text) {
                var div = document.createElement('div');
                div.className = 'result-box ' + className;

                var heading = document.createElement('div');
                heading.className = 'result-label';
                heading.textContent = label;
                div.appendChild(heading);

                var body = document.createElement('p');
                body.className = 'text';
                body.textContent = text;
                div.appendChild(body);

                return div;
            }

            // The withheld guidance stays hidden until the visitor explicitly asks to see it.
            function withheldSection(data) {
                var note = box('withheld-note', 'Anxiety-support guidance withheld', 'Ordinary anxiety-support guidance was withheld by the override. The real NextStep app would never show withheld guidance to the user during an emergency. It is available here for demonstration purposes only.');

                var withheld = box('withheld', 'Demonstration only: guidance withheld by the override', data.withheld_support_guidance);
                withheld.id = 'withheld-' + data.scenario_id;
                withheld.hidden = true;

                var toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'toggle';
                toggle.textContent = 'Show what was withheld';
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-controls', withheld.id);
                toggle.addEventListener('click', function () {
                    var show = withheld.hidden;
                    withheld.hidden = !show;
                    toggle.setAttribute('aria-expanded', show ? 'true' : 'false');
                    toggle.textContent = show ? 'Hide what was withheld' : 'Show what was withheld';
                });

                note.appendChild(toggle);
                note.appendChild(withheld);

                return note;
            }

            function render(target, data) {
                target.replaceChildren();

                if (!data.override) {
                    target.appendChild(box('support', 'Anxiety-support guidance shown', data.support_guidance));
                    target.appendChild(box('logged', 'No safety override', 'No emergency warning sign in this example, so nothing was logged.'));
                    return;
                }

                target.appendChild(box('override', '⚠ Safety override occurred: ' + data.override_label, data.emergency_guidance));
                target.appendChild(withheldSection(data));

                var logged = box('logged', 'Logged on the server', 'Recorded: a timestamp, scenario ID "' + data.scenario_id + '", and override category "' + data.override_category + '". Nothing else was recorded.');
                target.appendChild(logged);
            }

            document.querySelectorAll('button.run').forEach(function (button) {
                button.addEventListener('click', function () {
                    var target = button.parentElement.querySelector('.result');
                    button.disabled = true;

                    fetch(button.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            if (!response.ok) { throw new Error('Request failed'); }
                            return response.json();
                        })
                        .then(function (data) { render(target, data); })
                        .catch(function () {
                            target.replaceChildren(box('logged', 'Demonstration unavailable', 'The example could not be run. Please refresh the page and try again.'));
                        })
                        .finally(function () { button.disabled = false; });
                });
            });
        })();
    </script>

    <script>
        // Guided walkthrough: visitors only choose a predefined fictional example.
        // All text is inserted with textContent, never as HTML.
        (function () {
            var root = document.getElementById('guided-demo');
            if (!root) { return; }

            var token = document.querySelector('meta[name="csrf-token"]').content;
            var people = root.querySelectorAll('button.person');
            var errorBox = root.querySelector('.guided-error');
            var status = root.querySelector('.guided-status');
            var current = null;

            function el(tag, className, text) {
                var node = document.createElement(tag);
                if (className) { node.className = className; }
                if (text !== undefined) { node.textContent = text; }
                return node;
            }

            function show(step) {
                root.querySelectorAll('[data-panel]').forEach(function (panel) {
                    panel.hidden = panel.dataset.panel !== String(step);
                });

                root.querySelectorAll('[data-step]').forEach(function (item) {
                    var n = Number(item.dataset.step);
                    item.classList.toggle('is-current', n === step);
                    item.classList.toggle('is-done', n < step);
                    if (n === step) { item.setAttribute('aria-current', 'step'); } else { item.removeAttribute('aria-current'); }
                });

                status.textContent = 'Step ' + step + ' of 3';
                root.querySelector('[data-panel="' + step + '"] h3').focus();
            }

            function resultBadge(check, index) {
                if (check.result === 'yes') {
                    // The first two checks are emergency and crisis checks.
                    return el('span', 'check-result ' + (index < 2 ? 'check-yes-urgent' : 'check-yes'), 'Yes');
                }
                if (check.result === 'no') { return el('span', 'check-result check-no', 'No'); }
                return el('span', 'check-result check-skipped', 'Skipped');
            }

            function renderChecks(data, story) {
                root.querySelector('[data-panel="2"] h3').textContent = 'Step 2: What NextStep checks for ' + data.name;
                root.querySelector('.guided-story').textContent = story;

                var list = root.querySelector('.check-list');
                list.replaceChildren();
                data.checks.forEach(function (check, index) {
                    var item = el('li', check.result === 'skipped' ? 'is-skipped' : '');
                    item.appendChild(el('span', 'check-q', (index + 1) + '. ' + check.question));
                    item.appendChild(resultBadge(check, index));
                    list.appendChild(item);
                });

                var skipped = data.checks.some(function (check) { return check.result === 'skipped'; });
                var summary = 'Every check was “no”, so NextStep suggests anxiety-support tools.';
                if (skipped) {
                    summary = 'NextStep stopped at the first “yes”. The checks after it were skipped because a higher-priority step was already found.';
                } else if (data.outcome === 'professional') {
                    summary = 'No emergency or crisis signs were found, but this example should be checked by a healthcare professional soon.';
                }
                root.querySelector('.check-summary').textContent = summary;
            }

            function renderOutcome(data) {
                var box = root.querySelector('.outcome');
                box.className = 'outcome outcome-' + data.outcome;
                box.querySelector('.result-label').textContent = data.override
                    ? '⚠ Safety override: emergency guidance comes first'
                    : 'Suggested next step';
                box.querySelector('.outcome-title').textContent = data.label;
                box.querySelector('.outcome-guidance').textContent = data.guidance;

                var action = box.querySelector('.outcome-action');
                action.replaceChildren();
                action.hidden = true;
                if (data.outcome === 'emergency' || data.outcome === 'crisis') {
                    var call = el('a', 'btn ' + (data.outcome === 'emergency' ? 'btn-alert' : 'btn-violet'),
                        data.outcome === 'emergency' ? 'Call 911' : 'Call or text 988');
                    call.href = data.outcome === 'emergency' ? 'tel:911' : 'tel:988';
                    action.appendChild(call);
                    action.hidden = false;
                }

                var reasons = root.querySelector('.why .feature-list');
                reasons.replaceChildren();
                data.reasons.forEach(function (reason) { reasons.appendChild(el('li', '', reason)); });

                root.querySelector('.guided-logged').textContent = data.logged
                    ? 'Logged on the server: a timestamp, example ID “' + data.scenario_id + '”, and category “' + data.override_category + '”. Nothing else was recorded.'
                    : 'No safety override in this example, so nothing was logged.';
            }

            people.forEach(function (button) {
                button.addEventListener('click', function () {
                    var story = button.querySelector('.person-story').textContent;
                    errorBox.hidden = true;
                    people.forEach(function (b) { b.disabled = true; });

                    fetch(button.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            if (!response.ok) { throw new Error('Request failed'); }
                            return response.json();
                        })
                        .then(function (data) {
                            current = data;
                            renderChecks(data, story);
                            show(2);
                        })
                        .catch(function () {
                            errorBox.textContent = 'This example could not be loaded. Please refresh the page and try again.';
                            errorBox.hidden = false;
                        })
                        .finally(function () {
                            people.forEach(function (b) { b.disabled = false; });
                        });
                });
            });

            root.querySelectorAll('[data-action="result"]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!current) { return; }
                    renderOutcome(current);
                    show(3);
                });
            });

            root.querySelectorAll('[data-action="restart"]').forEach(function (button) {
                button.addEventListener('click', function () {
                    current = null;
                    show(1);
                });
            });
        })();
    </script>
</body>
</html>

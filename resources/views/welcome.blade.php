<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="NextStep is an AI-powered app in development to help people experiencing health anxiety figure out their next step. Coming soon.">
    <title>NextStep | A calmer way to figure out your next step</title>
    <style>
        :root {
            --blue-900: #12344d;
            --blue-700: #1f5f8b;
            --blue-100: #e3eff7;
            --teal-700: #1c7a70;
            --teal-500: #2a9d8f;
            --teal-100: #dff3f0;
            --neutral-900: #1f2a33;
            --neutral-700: #46545f;
            --neutral-300: #d5dde3;
            --neutral-100: #f4f7f9;
            --white: #ffffff;
            --alert-700: #a12b2b;
            --alert-100: #fdecec;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 1.0625rem;
            line-height: 1.65;
            color: var(--neutral-900);
            background: var(--neutral-100);
        }

        .container {
            width: 100%;
            max-width: 880px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        a { color: var(--blue-700); }

        h1, h2, h3 { line-height: 1.25; color: var(--blue-900); }

        h2 { font-size: 1.6rem; margin: 0 0 0.75rem; }

        h3 { font-size: 1.15rem; margin: 0 0 0.4rem; }

        p { margin: 0 0 1rem; }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, var(--blue-700), var(--teal-500));
            color: var(--white);
            text-align: center;
            padding: 4rem 0 4.5rem;
        }

        .hero h1 {
            color: var(--white);
            font-size: clamp(2.6rem, 8vw, 4rem);
            margin: 0 0 0.5rem;
            letter-spacing: -0.02em;
        }

        .hero .subtitle {
            font-size: clamp(1.15rem, 3.5vw, 1.5rem);
            margin-bottom: 1.5rem;
        }

        .hero .intro {
            max-width: 640px;
            margin: 0 auto 1rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .badge {
            display: inline-block;
            background: var(--white);
            color: var(--blue-900);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            margin-bottom: 1.75rem;
        }

        /* Sections */
        section { padding: 2.75rem 0; }

        .card {
            background: var(--white);
            border: 1px solid var(--neutral-300);
            border-radius: 14px;
            padding: 1.75rem;
        }

        .planned {
            display: inline-block;
            background: var(--teal-100);
            color: var(--teal-700);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.2rem 0.7rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            display: grid;
            gap: 0.75rem;
        }

        .feature-list li {
            background: var(--blue-100);
            border-left: 4px solid var(--teal-500);
            border-radius: 8px;
            padding: 0.8rem 1rem;
        }

        .disclaimer {
            border-left: 6px solid var(--blue-700);
            background: var(--blue-100);
        }

        .disclaimer p { margin: 0; font-weight: 500; }

        .emergency {
            border: 2px solid var(--alert-700);
            background: var(--alert-100);
        }

        .emergency h2 { color: var(--alert-700); }

        .emergency ul { padding-left: 1.25rem; margin: 0 0 1rem; }

        .emergency li { margin-bottom: 0.5rem; }

        .emergency a { color: var(--alert-700); font-weight: 700; }

        .note { font-size: 0.95rem; color: var(--neutral-700); margin: 0; }

        /* Demonstration */
        .demo-label {
            background: var(--blue-900);
            color: var(--white);
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .scenarios { display: grid; gap: 1rem; margin-top: 1.25rem; }

        .scenario {
            border: 1px solid var(--neutral-300);
            border-radius: 10px;
            padding: 1.1rem;
            background: var(--neutral-100);
        }

        .scenario p { margin-bottom: 0.75rem; }

        .scenario code { font-size: 0.85rem; color: var(--neutral-700); }

        button.run {
            font: inherit;
            font-weight: 600;
            color: var(--white);
            background: var(--blue-700);
            border: 0;
            border-radius: 8px;
            padding: 0.6rem 1.1rem;
            cursor: pointer;
        }

        button.run:hover, button.run:focus-visible { background: var(--blue-900); }

        button.run:disabled { opacity: 0.6; cursor: wait; }

        .result { margin-top: 1rem; }

        .result-box {
            border-radius: 8px;
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

        .logged { background: var(--white); border: 1px solid var(--neutral-300); font-size: 0.92rem; }

        .logged code { word-break: break-word; }

        footer {
            text-align: center;
            font-size: 0.9rem;
            color: var(--neutral-700);
            padding: 2rem 0 3rem;
        }

        @media (max-width: 600px) {
            .hero { padding: 3rem 0 3.25rem; }
            section { padding: 2rem 0; }
            .card { padding: 1.25rem; }
            button.run { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="hero">
        <div class="container">
            <h1>NextStep</h1>
            <p class="subtitle">A calmer way to figure out your next step.</p>
            <div class="badge">Coming Soon</div>
            <p class="intro">
                NextStep is an AI-powered app being developed to help people experiencing health anxiety
                decide what to do when physical symptoms feel overwhelming.
            </p>
            <p class="intro"><strong>NextStep will not diagnose medical conditions.</strong></p>
        </div>
    </header>

    <main>
        <section id="health-anxiety">
            <div class="container">
                <div class="card">
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
            </div>
        </section>

        <section id="how-it-will-help">
            <div class="container">
                <div class="card">
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

        <section id="safety-demo">
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
    </main>

    <footer>
        <div class="container">
            <p>NextStep is in development. Coming soon.</p>
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

            function render(target, data) {
                target.replaceChildren();

                if (!data.override) {
                    target.appendChild(box('support', 'Anxiety-support guidance shown', data.support_guidance));
                    target.appendChild(box('logged', 'No safety override', 'No emergency warning sign in this example, so nothing was logged.'));
                    return;
                }

                target.appendChild(box('override', '⚠ Safety override occurred: ' + data.override_label, data.emergency_guidance));
                target.appendChild(box('withheld', 'Anxiety-support guidance withheld by the override', data.withheld_support_guidance));

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
</body>
</html>

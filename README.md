# NextStep

**Live website:** https://dbknfeuu0b0u9.cloudfront.net

The NextStep Laravel app is hosted on AWS Elastic Beanstalk and served to visitors through Amazon CloudFront. Please use the HTTPS address above; the safety demonstrations do not work on the plain-HTTP Elastic Beanstalk address.

NextStep is an AI-powered app being developed to help people experiencing health anxiety decide what to do when physical symptoms feel overwhelming. It will not diagnose medical conditions.

## The Problem

People with health anxiety can become overwhelmed when they notice a physical symptom. They may repeatedly Google symptoms, search social media, or seek reassurance, which can make their anxiety worse. In these moments, it can be difficult to determine what they should actually do next.

## Who NextStep Helps

NextStep is designed for people who experience health anxiety and want a calmer, more structured way to decide their next step.

## What the AI Will Do (Planned)

The only AI feature so far is a small, optional proof of concept that is now working on the live website (see [AI Explanation Demonstration](#ai-explanation-demonstration-proof-of-concept)). The plan is for the AI to focus on one main question: "What should I do right now?" Instead of diagnosing a user or providing a long list of possible illnesses, the AI will ask a small number of safety-focused questions and guide the user toward an appropriate next step. When emergency warning signs are not identified, NextStep will also guide users toward strategies to help manage anxiety and reduce repeated symptom checking.

## Safety Disclaimer

NextStep is an educational and supportive tool in development. It does not diagnose medical conditions, provide medical advice, or replace doctors, therapists, or other qualified healthcare professionals. Not identifying warning signs does not guarantee that a symptom is harmless.

## Emergency Information

These phone numbers are for the United States.

- Medical emergency: Call 911 or go to the nearest emergency room.
- Mental health or suicide-related crisis: Call or text 988.
- Poisoning or possible medication overdose: Call Poison Control at 1-800-222-1222.
- If someone is unconscious, having trouble breathing, or experiencing an immediate emergency, call 911.

## Guided Walkthrough

The homepage includes a three-step guided walkthrough, labeled "Walk through an example," that shows how NextStep is designed to choose a safe next step. It is an educational demonstration only. It uses four fictional, predefined examples, the safety checks never use AI, and visitors are never asked about their own health. The page has no text boxes or forms, so visitors cannot enter symptoms or personal health information.

1. **Choose an example.** The visitor selects one of four fictional people: Maya, Jordan, Sam, or Alex.
2. **See the safety checks.** NextStep answers three questions in a fixed order. The answers are fixed for each example, and the visitor does not answer them.
   1. Are there emergency warning signs?
   2. Are there crisis or safety concerns?
   3. Does this need a healthcare professional soon?

   The first "yes" decides the next step, and any remaining checks are marked as skipped.
3. **Receive a next step.** The result explains which step was chosen and why. Every result also shows the safety disclaimer and the emergency phone numbers (911, 988, and Poison Control).

| Fictional example | Situation | Next step | Safety override |
| --- | --- | --- | --- |
| Maya (`guided-maya-headache`) | A mild headache after a long day of studying, with repeated symptom checking online | Try anxiety-support tools | None |
| Jordan (`guided-jordan-cough`) | A cough that has lasted three weeks, with no warning signs | Contact a healthcare professional soon | None |
| Sam (`guided-sam-chest-pressure`) | Sudden chest pressure with sweating and shortness of breath | Call 911 now | `medical_emergency` |
| Alex (`guided-alex-hopeless`) | Feeling hopeless, with thoughts of not wanting to be alive | Call or text 988 now | `mental_health_crisis` |

**Emergencies come first.** Emergency warning signs are always checked first, followed by crisis concerns. When either is present, emergency or crisis guidance takes priority over ordinary anxiety-support advice, and the later checks are skipped. The 911 and 988 wording is shared with the Safety Override Demonstration below, so both demonstrations always give the same emergency guidance. Even when no warning signs are found, the result reminds visitors that this does not guarantee a symptom is harmless.

**Logging.** When a visitor selects Sam or Alex, the safety override is logged on the server at the moment the example is selected. Each log entry uses the same format and file as the Safety Override Demonstration (see [Override logging](#override-logging)): a timestamp, the predefined example ID, and the override category only. Maya and Jordan do not trigger an override, so nothing is logged for them.

The examples are defined in `config/guided_demo.php`, the decision rule is in `app/Safety/NextStepDecider.php`, the server endpoint is in `app/Http/Controllers/GuidedDemoController.php`, and the tests are in `tests/Feature/GuidedDemoDecisionTest.php`, `tests/Feature/GuidedDemoEndpointTest.php`, and `tests/Feature/GuidedDemoPageTest.php`.

## AI Explanation Demonstration (Proof of Concept)

Step 3 of the guided walkthrough has an optional "Explain this step with AI" button. It is NextStep's first AI interaction.

**Status: working on the live website.** The AI explanation is switched on at https://dbknfeuu0b0u9.cloudfront.net and uses [Groq](https://groq.com) with the `openai/gpt-oss-20b` model, which is available on Groq's free plan. In the code it is still **off by default**: it only runs where `AI_DEMO_ENABLED=true` and a Groq API key are set, as they are on the live server.

**The four fictional examples**

| Fictional example | Next step chosen by the safety rules | Uses AI? |
| --- | --- | --- |
| Maya: mild headache after studying, with repeated symptom checking | Try anxiety-support tools | Yes, AI explains the chosen step |
| Jordan: a cough that has lasted three weeks, with no warning signs | Contact a healthcare professional soon | Yes, AI explains the chosen step |
| Sam: sudden chest pressure, sweating, and shortness of breath | Call 911 now | **Never.** Fixed emergency guidance only |
| Alex: feeling hopeless, with thoughts of not wanting to be alive | Call or text 988 now | **Never.** Fixed crisis guidance only |

**Safety rules run before AI**

1. The visitor picks a fictional example. Only its ID is sent to the server, never anything the visitor types.
2. The server runs the same safety rules as the guided walkthrough (`NextStepDecider`) **before** anything else. The rules, not the AI, choose the next step.
3. **Emergency (Sam) and crisis (Alex) examples never reach the AI.** The server returns the fixed 911 or 988 guidance before any AI code runs, and the override is logged in the usual three-field format. The AI code also refuses these outcomes on its own as a second check, and the page hides the AI button for them.
4. For Maya and Jordan only, the server asks the AI to explain the step that was already chosen, in 2–3 calm sentences. The prompt is built only from the predefined example in `config/guided_demo.php`. The AI cannot change the step, and the fixed next step, disclaimer, and emergency numbers stay on screen directly below the explanation.
5. Every reply, new or saved, must pass a safety filter before it is shown. Replies that diagnose or name conditions, mention medication, falsely reassure (for example "nothing to worry about" or "doesn't need to see a doctor"), or include links or numbers of three or more digits are discarded. The filter is in `config/ai_demo.php`.

**Daily limit**

- The server allows up to 50 AI calls per day (UTC) by default, set with `AI_DEMO_DAILY_LIMIT`.
- Every request that reaches Groq counts, including ones that fail or are filtered. Showing a saved answer does not count.
- When the limit is reached, the page says "The AI explanation has reached its daily limit" and the fixed next step still applies. The count starts again the next day.
- All visitors also share a limit of 20 AI requests a minute. Above it, the page says the AI explanation could not be loaded.
- Groq's own free-plan limits are a final backstop. Use the free plan with no payment method on file.

**Caching (saved answers)**

- A reply that passes the filter is saved on the server for 24 hours, separately for Maya and Jordan. Everyone who asks during that time sees the same saved reply, and Groq is not called again.
- Saved replies are checked against the filter again every time they are shown.
- Failed or filtered replies are never saved, so the next click asks Groq again.
- Saved replies are stored in the server's file cache, so a new deployment may clear them.

**Occasional failed responses**

The AI words its reply a little differently each time. Most replies pass the filter, but sometimes one uses a blocked word (for example "condition" or "infection") and is discarded. Calls can also fail when Groq returns an error or rate limit, takes longer than 15 seconds, or sends an empty, cut-off, or overlong answer (over 1,000 characters).

In each of these cases the page shows "The AI explanation is not available right now. The next step above was chosen by NextStep's safety rules and still applies." This is the intended safe fallback, not a safety failure: nothing unfiltered is shown, and the next step does not change. Trying again usually works, and a successful reply is then saved for 24 hours.

**Other cost settings**

Answers are capped at 1,024 tokens (including the model's hidden reasoning), the temperature is low (0.2) for more predictable replies, and there are no automatic retries.

**API key**

- The key is read from `GROQ_API_KEY` on the server only and is never sent to the browser.
- Locally, put it in `.env`, which Git ignores.
- In production, set it in the Elastic Beanstalk console's environment properties, never in `.ebextensions` or any committed file.
- Tests force the key to be empty and use a fake AI, so `php artisan test` never makes a real API call.

The settings are in `config/ai_demo.php`, the safety-first endpoint is in `app/Http/Controllers/AiDemoController.php`, the AI code is in `app/Ai/`, and the tests are in `tests/Feature/AiDemoTest.php`.

## Safety Override Demonstration

The homepage includes a small, clearly labeled safety override demonstration. It is a demonstration, not a working AI symptom checker. It uses four fictional, predefined examples. Visitors cannot type anything in, and no AI is involved.

| Scenario ID | What it shows | Override category |
| --- | --- | --- |
| `demo-ordinary-worry` | Ordinary anxiety-support guidance is shown. | None |
| `demo-emergency-warning` | Emergency guidance replaces the anxiety-support guidance. | `medical_emergency` |
| `demo-override-priority` | The fictional person asks only for a relaxation tip, but a warning sign is present, so emergency guidance takes priority. | `mental_health_crisis` |
| `demo-impending-doom` | A sudden sense of impending doom with a racing heart and trouble breathing. It can occur with panic attacks but also with serious medical emergencies, so emergency evaluation (call 911) takes priority and relaxation guidance is withheld. | `medical_emergency` |

When an override happens, the page shows a visible "Safety override occurred" notice, hides the withheld anxiety-support guidance behind a demonstration-only "Show what was withheld" toggle, and confirms the event was logged.

### Override logging

Each override is logged on the server to `storage/logs/safety-override.log`, one line per event:

```json
{"timestamp":"2026-10-09T14:59:44+00:00","scenario_id":"demo-emergency-warning","override_category":"medical_emergency"}
```

- The log records only a timestamp, the predefined scenario ID, and the override category.
- It does not record names, IP addresses, real symptoms, personal health information, or other identifying details. Anything sent with the request is ignored.
- The log file is outside the public web folder, so it cannot be viewed from the website, and it is ignored by Git.

The examples are defined in `config/safety_demo.php`, the logic is in `app/Http/Controllers/SafetyDemoController.php`, and the tests are in `tests/Feature/SafetyDemoTest.php`. Run them with `php artisan test`.

## Future AI Safety Requirements

No AI chatbot has been built yet, and visitors still cannot type anything. The AI explanation demonstration above already follows these rules. When more AI features are developed:

- The medical disclaimer must appear next to every AI response.
- Relevant emergency referrals must appear next to every AI response.
- Emergency warnings must take priority over anxiety-management advice.
- Safety overrides must be logged without unnecessary personal information.
- The AI must not diagnose medical conditions.

## HTTPS

Visitors connect to the live website over HTTPS through CloudFront. The connection from CloudFront to the Elastic Beanstalk server still uses HTTP, so the site is **not yet encrypted end to end**. Securing that connection is required before NextStep adds real AI features or collects any personal health information.

### CloudFront-only access (origin verification)

Visitors should only reach NextStep through CloudFront. CloudFront can add a secret `X-Origin-Verify` header to every request it sends to the server. When `ORIGIN_VERIFY_SECRET` is set, NextStep refuses any request without that header (a plain `403 Forbidden`, with no cookies), so the Elastic Beanstalk address can't be used to skip CloudFront. CloudFront overwrites the header if a visitor tries to send their own.

- It is **off** until `ORIGIN_VERIFY_SECRET` is set. Leave it empty locally.
- In production, set it only in the Elastic Beanstalk console, and only **after** CloudFront is already sending the header.
- `ORIGIN_VERIFY_SECRET_PREVIOUS` lets the old value keep working while the secret is being changed.
- The health check at `/up` works without the header.
- The header still travels over the unencrypted CloudFront-to-server link, so this is not a replacement for HTTPS on that link.

The check is in `app/Http/Middleware/VerifyOriginHeader.php`, its settings are in `config/origin.php`, and the tests are in `tests/Feature/OriginVerifyTest.php`.

Every response also sends standard browser security headers (`app/Http/Middleware/SecurityHeaders.php`): a content security policy that only allows the site's own content and its two nonce-tagged scripts, protection against being embedded in other sites, `nosniff`, a referrer policy, a permissions policy that turns off camera, microphone, and location, and, in production, a header telling browsers to always use HTTPS.

## Running NextStep Locally

1. Clone the GitHub repository.
2. Open the NextStep project folder.
3. Install the required Laravel dependencies.
4. Run `php artisan serve`.
5. Open `http://127.0.0.1:8000` in your browser.

## Current Status
NextStep is currently in development, with a live guided walkthrough and working AI explanations for fictional examples. It includes information about health anxiety, the planned features, the safety disclaimer, emergency information, the guided walkthrough, and the safety override demonstration. The only AI feature is the optional AI explanation proof of concept, which is working on the live website using Groq for the Maya and Jordan examples. Sam and Alex never use AI. Visitors access NextStep through HTTPS using Amazon CloudFront.

Reagan O'Mara, CUA Busch School AI Vibe Coding Contest, Fall 2026

# NextStep

**Live website:** http://nextstep-reagan.us-east-2.elasticbeanstalk.com

The NextStep Laravel app is hosted on AWS Elastic Beanstalk.

NextStep is an AI-powered app being developed to help people experiencing health anxiety decide what to do when physical symptoms feel overwhelming. It will not diagnose medical conditions.

## The Problem

People with health anxiety can become overwhelmed when they notice a physical symptom. They may repeatedly Google symptoms, search social media, or seek reassurance, which can make their anxiety worse. In these moments, it can be difficult to determine what they should actually do next.

## Who NextStep Helps

NextStep is designed for people who experience health anxiety and want a calmer, more structured way to decide their next step.

## What the AI Will Do (Planned)

NextStep does not have working AI features yet. The plan is for the AI to focus on one main question: "What should I do right now?" Instead of diagnosing a user or providing a long list of possible illnesses, the AI will ask a small number of safety-focused questions and guide the user toward an appropriate next step. When emergency warning signs are not identified, NextStep will also guide users toward strategies to help manage anxiety and reduce repeated symptom checking.

## Safety Disclaimer

NextStep is an educational and supportive tool in development. It does not diagnose medical conditions, provide medical advice, or replace doctors, therapists, or other qualified healthcare professionals. Not identifying warning signs does not guarantee that a symptom is harmless.

## Emergency Information

These phone numbers are for the United States.

- Medical emergency: Call 911 or go to the nearest emergency room.
- Mental health or suicide-related crisis: Call or text 988.
- Poisoning or possible medication overdose: Call Poison Control at 1-800-222-1222.
- If someone is unconscious, having trouble breathing, or experiencing an immediate emergency, call 911.

## Safety Override Demonstration

The homepage includes a small, clearly labeled safety override demonstration. It is a demonstration, not a working AI symptom checker. It uses three fictional, predefined examples. Visitors cannot type anything in, and no AI is involved.

| Scenario ID | What it shows | Override category |
| --- | --- | --- |
| `demo-ordinary-worry` | Ordinary anxiety-support guidance is shown. | None |
| `demo-emergency-warning` | Emergency guidance replaces the anxiety-support guidance. | `medical_emergency` |
| `demo-override-priority` | The fictional person asks only for a relaxation tip, but a warning sign is present, so emergency guidance takes priority. | `mental_health_crisis` |

When an override happens, the page shows a visible "Safety override occurred" notice, shows the anxiety-support guidance that was withheld, and confirms the event was logged.

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

No real AI chatbot has been built yet. When AI features are developed:

- The medical disclaimer must appear next to every AI response.
- Relevant emergency referrals must appear next to every AI response.
- Emergency warnings must take priority over anxiety-management advice.
- Safety overrides must be logged without unnecessary personal information.
- The AI must not diagnose medical conditions.

## HTTPS

The live website currently uses HTTP. **HTTPS is required before judging** and has not been set up yet.

## Running NextStep Locally

1. Clone the GitHub repository.
2. Open the NextStep project folder.
3. Install the required Laravel dependencies.
4. Run `php artisan serve`.
5. Open `http://127.0.0.1:8000` in your browser.

## Current Status
NextStep is currently in development and the website is a Coming Soon page. It includes information about health anxiety, the planned features, the safety disclaimer, emergency information, and the safety override demonstration. The AI features are not built yet.

Reagan O'Mara, CUA Busch School AI Vibe Coding Contest, Fall 2026

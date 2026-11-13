# Thumela — All-features PHP + MySQL demo

A voice-first accessible finance prototype for the Node Engine Agentic Finance track.

## Included in this build

- Registration, login, logout and per-user PHP sessions.
- Password hashing and per-user demo wallets.
- Personal first-name greeting and saved voice-language / response-style preferences.
- Browser voice input and spoken output where supported by the device/browser.
- Voice/text command assistant with multi-action routing: balance, history, saved-recipient payments, simulated airtime/utilities, savings-goal creation and contributions, payment requests, and help/capability discovery.
- Saved recipients, including personal contacts.
- Payment quote, fee display, explicit approval and demo receipt.
- Recent and full transaction activity.
- Savings goals and demo contributions from available wallet balance.
- Simulated airtime, electricity, water and internet bill payments.
- Payment request codes that can be copied and shared manually.
- Split-payment calculator.
- Session-authenticated MCP-style JSON-RPC endpoint at `backend/api/mcp.php`.
- MCP tools: `get_wallet_balance`, `list_recipients`, `get_transaction_history`, `list_savings_goals`, `create_savings_goal`, `contribute_savings_goal`, `create_payment_request`, `pay_bill`, `prepare_payment`.

## IMPORTANT: fresh database setup

This build's `database/thumela.sql` starts with `DROP DATABASE IF EXISTS thumela`. Importing it **deletes the existing Thumela database**, including registered accounts, demo balances, recipients and transaction history.

1. If you need existing demo records, export a backup in phpMyAdmin first.
2. Extract this folder to `C:\xampp\htdocs\Thumela_PHP_All_Features`.
3. Start Apache and MySQL in XAMPP.
4. Open `http://localhost/phpmyadmin`.
5. Select Import, choose `database/thumela.sql`, then click Go.
6. Open `http://localhost/Thumela_PHP_All_Features/`.
7. Register a fresh demo account.

XAMPP defaults are configured in `backend/config/database.php` (host `127.0.0.1`, database `thumela`, user `root`, blank password). Change these for any other environment.

## Example demo flow

1. Register and choose a voice language.
2. Try `How much is my balance?`.
3. Add a recipient, or use the sample recipient Mum.
4. Try `Send 25 USDT to Mum`.
5. Review recipient, wallet address, amount, fee and total.
6. Approve the demo payment; then inspect the receipt and transaction history.
7. Create a savings goal and contribute an amount.
8. Simulate an airtime or utility bill payment.
9. Create a payment request code and copy it.
10. Use the split-payment calculator.
11. Inspect the MCP endpoint by sending JSON-RPC `tools/list` or `tools/call` requests while logged in.

## MCP-style endpoint

Endpoint: `POST /backend/api/mcp.php`

Example request body:
```json
{"jsonrpc":"2.0","id":1,"method":"tools/list"}
```

Example tool call:
```json
{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"get_wallet_balance","arguments":{}}}
```

The endpoint is local, session-authenticated JSON-RPC exposing MCP-shaped tools. The browser-side command router coordinates these tools for a deterministic demo assistant. This is a hackathon integration scaffold; it is not a remotely hosted MCP service and is not connected to an external LLM agent runtime yet.

## Voice and accessibility notes

Available language preferences: South African English (`en-ZA`), isiZulu (`zu-ZA`), isiXhosa (`xh-ZA`), Shona (`sn-ZW`) and Nigerian English (`en-NG`). Browser speech recognition and speech synthesis depend on the installed browser/device engines and voices. Selecting a language does not install a language model or guarantee a native accent. Keyboard controls, labels, live status regions and a skip link are included; test with actual screen readers and blind/low-vision participants.

## What is still simulated / not production-ready

- No real USDT or fiat funds are held or transferred.
- Payment and bill flows update a local demo ledger only.
- The assistant uses deterministic command parsing and MCP-style tool orchestration, not a connected LLM that reasons autonomously. Some commands require a browser confirmation prompt; this must be tested with the target assistive technology.
- MCP-style JSON-RPC tools are exposed, but there is no production MCP host/client deployment or LLM tool-calling runtime.
- No live blockchain transaction signing, broadcasting, gas estimation or receipt verification is implemented.
- No live FX rates, bank/mobile-money rails, merchant settlement, QR payment acceptance or dispute process.
- Payment request codes are demo identifiers, not public payment URLs and cannot receive funds.
- Authentication still needs production hardening: CSRF protection, rate limiting, email verification, password reset, MFA, audit logging, secure deployment configuration and security testing.

Never use real passwords, wallet seed phrases or private keys in this demo. Do not present simulated transaction receipts as proof of onchain settlement.


## Voice-first conversation update

The main financial assistant now offers a large **Start talking to Thumela** control. After the user activates it, Thumela speaks a welcome prompt, listens for one utterance at a time, processes the request, speaks the response, and then resumes listening. The user can say "stop listening", "stop voice", "goodbye" or "end conversation", or press the same control to stop.

Typing is hidden by default behind **Show typing option**. It remains available as an optional fallback, not as the primary interaction. The sign-in screen includes spoken instructions. Passwords should not be dictated aloud; use the device password manager and screen reader or an approved secure credential method.

### Browser support and privacy

- Speech recognition is browser/device dependent. The Web Speech recognition API is not supported in every browser and may rely on a cloud recognition service. Test on the actual target phone and browser, with microphone permission granted.
- Speech synthesis uses the device/browser voices. A selected locale does not guarantee a matching African-language voice or accent is installed.
- The microphone is activated only after the user presses Start talking. The user can stop the voice session with speech or the on-screen control.
- The app does **not** claim that speech recognition authenticates the speaker. Recognising words is not the same as verifying a person's identity.
- Face recognition is not included in this browser demo. Do not store face images or treat a camera preview as biometric verification. For a production mobile app, use the operating system's secure biometric/passkey facility (for example, platform authentication) with a non-biometric fallback, and keep biometric templates on the device where possible.
- Before any financial action, the app must explain the recipient, amount, fees and effect, and require explicit confirmation. The demo's simulated transactions must never be presented as real transfers.


## Multilingual and African-locale voice support (demo)

The account creation screen and signed-in voice settings now let each user choose a preferred language. The preference is saved to the user's profile in MySQL and restored on the next login. The same selection controls the interface language pack, speech-recognition locale request, and text-to-speech locale request.

Language choices included:
- South African English (`en-ZA`), Afrikaans (`af-ZA`), isiZulu (`zu-ZA`), isiXhosa (`xh-ZA`), Sesotho (`st-ZA`), Setswana (`tn-ZA`), Sepedi (`nso-ZA`), Xitsonga (`ts-ZA`), Tshivenda (`ve-ZA`), siSwati (`ss-ZA`), isiNdebele (`nr-ZA`), Shona (`sn-ZW`) and Nigerian English (`en-NG`).

The core interface has translated labels and controls for the main language packs. Tshivenda and isiNdebele are currently selectable voice locales but their interface translations are still in progress. The voice assistant has translated welcome, listening, balance and history phrases for Afrikaans, isiZulu, isiXhosa, Sesotho and Shona, plus a small set of translated balance/history command phrases. Other complex agent instructions and financial explanations may still be delivered in English; this is a multilingual MVP, not yet a complete professional translation of every workflow.

### African voice note

Thumela now asks the browser/device for a voice matching the user's selected African language/locale and prefers an exact locale match. The app reports which installed voice it found. It cannot manufacture an African accent or guarantee one: speech voices and recognition languages are supplied by the operating system/browser. If the device has no matching voice, it may fall back to its default voice. For a consistent natural African voice across devices, a production release will need a licensed African-language speech provider or a self-hosted speech service, with the appropriate API credentials and privacy review.

Speech recognition support also varies by browser and locale. Test each target language on the actual Android/iOS devices before presenting it as fully supported. Users should be told when a language is not available, and should have a human-assisted or accessible fallback.


## Voice and agent update (13 November 2026)

The agent now responds to common greetings with friendly emoji-enhanced messages and can read saved recipients and savings goals aloud. It supports the existing demo workflows for wallet balance, transaction history, transfer preparation, savings, payment requests, bill payments and expense splitting. The signed-in user can switch the preferred language in the app; the preference is saved to the PHP profile.

Speech is provided by the user's browser and operating system. Thumela requests the selected African/regional locale and uses an installed matching voice if one exists. This is not a bundled professional African voiceover. Actual language coverage depends on the device/browser. The agent is not a newly trained ML model or connected hosted LLM; it is an intent-based demo agent using backend tools. Real funds are not transferred.


## Dashboard refresh

The landing screen offers a voice-first guidance choice or a visual-control choice. After sign-in, the interface is split into navigable pages: Home, Send, Receive, People, Savings, Bills, Activity, Tools and Settings. Voice commands can navigate between these views. This improves access to existing demo features; it does not add real-money rails or turn the deterministic command router into a trained model.
\n\n## Latest interface update\n\n- Added the supplied Thumela animated logo as a one-shot opening screen with a skip control.\n- Added a persistent left-side navigation rail on desktop; it becomes a horizontal scrollable navigation on smaller screens.\n- Kept the authentication, dashboard, payment approval and demo-only limitations intact.\n

## USSD / basic-phone access update (13 November 2026)

- Added a navigable USSD Access page to demonstrate the basic-phone menu journey.
- The simulator supports menu navigation, saved-recipient selection, amount entry, balance/history display, a spoken-menu button and a clear demo-only completion state.
- The simulator does not send network callbacks or move funds. It is not a live USSD shortcode.
- Live integration requires a provider-assigned South African service code, a public HTTPS PHP callback, session handling, verified phone-to-account binding and transaction-specific secure confirmation.
- A blind user on a feature phone may not be able to read a text-only USSD menu. Audio-assisted IVR must be separately sourced and tested; availability of a local voice provider must be confirmed.

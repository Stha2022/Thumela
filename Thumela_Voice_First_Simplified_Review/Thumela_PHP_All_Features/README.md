# Thumela — Animated Welcome + Accessible Agentic Finance Demo

A voice-first accessible finance prototype positioned for the W3Node Node Engine Agentic Finance track.

## New in this update

- The supplied Thumela logo GIF is the first screen shown on arrival.
- The intro animation has been converted to a one-shot GIF so it plays once (about 3 seconds) rather than looping indefinitely.
- Users can skip the intro; the screen reader is given a labelled welcome dialog.
- After the intro, the existing authentication flow is shown (or an existing authenticated session resumes).
- After login, desktop users get a persistent left-side navigation rail. On smaller screens it becomes a horizontally scrollable navigation bar.
- The main dashboard pages remain separate: Home, Send, Receive, People, Savings, Bills, Activity, Tools and Settings.

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

The endpoint is local, session-authenticated JSON-RPC exposing MCP-shaped tools. The browser-side command router coordinates these tools for a deterministic demo assistant. This is a local MCP-style integration scaffold; it is not a remotely hosted MCP deployment and is not connected to an external LLM agent runtime yet.

## Voice and accessibility notes

Available language preferences: South African English (`en-ZA`), isiZulu (`zu-ZA`), isiXhosa (`xh-ZA`), Shona (`sn-ZW`) and Nigerian English (`en-NG`). Browser speech recognition and speech synthesis depend on the installed browser/device engines and voices. Selecting a language does not install a language model or guarantee a native accent. Keyboard controls, labels, live status regions and a skip link are included; test with actual screen readers and blind/low-vision participants.

## Agentic Finance track positioning

W3Node describes Agentic Finance as autonomous agents that transact, manage treasury and execute financial decisions onchain, powered by MCPs. Thumela demonstrates the accessible interaction layer and a local MCP-style tool interface: the assistant can inspect a wallet, list recipients, prepare quotes and coordinate approved demo actions. The current build does **not** broadcast onchain transactions. A live testnet wallet/signing flow, onchain execution and transaction-receipt verification remain required before claiming end-to-end onchain agentic execution.

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


## Latest voice and agent improvements

- Friendly greetings now receive warm, emoji-supported responses (👋🏾 💚) in the selected language where a translation is included.
- The voice agent can read saved recipients and savings goals aloud, check balances and history, prepare transfers, create savings goals, contribute to goals, create payment requests, and process simulated bill payments.
- Users can change the language from the in-app **App language & voice** selector after signing in. The selected preference is saved to their profile.
- Voice input uses the selected locale where the browser supports it; spoken output requests the matching locale and uses a matching installed device voice when available.

### Important voice limitation

This project does not contain a separately recorded or AI-cloned African voice. The browser's speech-synthesis engine supplies the actual voice. For an authentic South African/African voice, install the desired language/voice in the device operating system and select that language in Thumela. Availability differs by device and browser. English (South Africa), Afrikaans, isiZulu, isiXhosa, Sesotho, Setswana, Sepedi, Xitsonga, Tshivenda, siSwati, isiNdebele, Shona and Nigerian English are offered as locale choices, but not every device provides speech recognition and speech output for every locale.

### Important AI limitation

The current agent is an extensible intent-routing assistant with PHP/MCP-style tools; it is **not a newly trained machine-learning model or a connected hosted LLM**. It can carry out the supported demo workflows through the backend. Any operation that changes demo funds must still be explicitly approved. Real-money transfers, identity verification, and production wallet signing are not enabled.


## Dashboard and onboarding refresh

- The landing page now offers **Use voice-first mode** and **Continue visually**. Voice-first mode speaks the sign-in guidance, while passwords remain entered through a secure password manager or screen reader rather than spoken aloud.
- After login, the app opens a multi-page dashboard: Home, Send, Receive, People, Savings, Bills, Activity, Tools and Settings.
- The user can move between pages using the navigation bar or voice commands such as "open my savings", "take me to activity", and "go to settings".
- Voice and visual interaction are complementary. The same account and financial tools are available regardless of interaction preference.
- The app speaks a friendly welcome after login. Greeting replies include friendly emoji in the visual agent workspace.
- This remains a deterministic demo agent with MCP-style tools, not a newly trained AI model or a hosted LLM agent. It can perform only the supported workflows described above, and financial changes still require explicit confirmation.


## USSD access prototype (new)

The dashboard includes a **USSD access** page with an interactive basic-phone menu simulator. It demonstrates menu navigation, recipient selection, amount entry, balance and recent-activity screens, plus a spoken-menu demonstration using the device's installed speech voice. The simulator does not contact a mobile network, does not use a live shortcode and never moves funds.

### Live South African integration roadmap

1. Select a telecom/USSD aggregator and request a South African USSD service code.
2. Deploy the PHP callback over a publicly reachable HTTPS endpoint; a telecom provider cannot call a developer's `localhost` directly.
3. Handle provider POST callbacks and USSD session IDs, with server-side session state and strict timeouts.
4. Link a verified mobile number to an existing Thumela account. Do not trust the caller ID alone as authentication.
5. For sensitive actions, authenticate with a secure PIN/OTP strategy and require a transaction-specific confirmation that repeats recipient, amount and fees.
6. Route the request into the same agent/MCP financial services as the app. USSD must not bypass the agent's policy checks or approval gate.
7. For blind users on basic phones, investigate a compatible local IVR provider for audio menus. A text-only USSD menu is not independently accessible to someone who cannot read it. Confirm South African voice-service availability and pricing before selecting a provider.

The local simulator is intended for the hackathon demonstration. A live shortcode, provider account, public callback deployment, phone-number verification and security review are still required for a real USSD service.


## Accessibility-first interface update

The dashboard has been simplified around six primary destinations: Home, Send money, Receive money, Trusted people, Transactions, and Accessibility. The voice assistant is the primary interaction for blind and low-vision users. Controls have larger targets, stronger focus indicators, clearer labels, and a less crowded layout. Advanced prototype cards and the USSD simulator are intentionally removed from the primary user journey; their backend/demo code is retained in the project for later development.

Important: browser speech recognition and speech synthesis depend on the user's browser, operating system, installed language packs, and microphone permissions. A selected African language/locale is not a guarantee that a matching voice is installed. Voice matching is not identity authentication. All current payment execution remains a simulation unless a separately configured wallet and testnet integration is added.

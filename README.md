Thumela --- Voice-First Accessible Finance
Money management that starts with your voice.

Thumela is a voice-first financial assistant prototype designed to make
everyday digital money tasks more accessible to blind and low-vision
people in South Africa. Instead of relying only on navigating complex
screens, a user can speak or type a request, receive guided assistance,
review the details and remain in control of the final decision.
Demo video
Watch the Thumela demo: https://youtu.be/lpF8twpVnAU
The problem
Many digital financial services rely heavily on visual navigation, small
text, dense screens and multi-step forms. This can make routine tasks
--- such as checking a balance, finding a recipient or preparing a
transfer --- unnecessarily difficult for people who are blind or have
low vision.

Financial interactions also require trust. Users need to understand who
a payment is for, how much it costs and what will happen before they
proceed.
Our solution
Thumela brings common financial tasks into a guided, conversational
experience.

A user can ask Thumela to: - Check their demo wallet balance. - View
saved recipients or "trusted people". - Review transaction history. -
Prepare a payment quote and review the recipient, amount, fees and
total. - Explore additional demo workflows such as savings goals,
payment requests and simulated bill payments.

The assistant can interpret requests and use authorised application
tools to retrieve information or prepare a quote. The user remains in
control: the AI does not approve or independently execute a payment.
Key features
- Voice-first interaction: browser speech recognition and spoken
  responses where supported by the device and browser.
- Alternative input: users can type requests or use their device's
  keyboard voice-typing feature where available.
- Accessible interface: large touch targets, clear labels,
  uncluttered task navigation and read-aloud support.
- Financial assistant: natural-language routing to balance,
  recipient, history and payment-preparation tools.
- Human approval boundary: payment details are presented for
  review before a user confirms a demo action.
- Demo financial workflows: recipients, transaction records,
  savings goals, payment requests and simulated bill payments.
- Installable PWA: can be added to a supported phone or desktop
  browser from an HTTPS-hosted deployment.
- Stellar Testnet exploration: separate wallet and native XLM
  testnet payment functionality is included in the source and requires
  hands-on wallet testing.
How it works
1. The user speaks or types a financial request.
2. Thumela interprets the request and identifies the appropriate task.
3. The application calls the relevant authenticated backend tool.
4. Thumela explains the result or prepares a payment quote.
5. The user reviews the details and decides whether to continue.
Technology
- HTML, CSS and JavaScript
- PHP 8.1+
- MySQL / MariaDB
- OpenAI Responses API integration (optional; requires an API key and
  API billing)
- Browser Web Speech APIs, where supported
- Progressive Web App (PWA)
- Capacitor Android packaging scaffold
- Stellar SDK and Stellar Testnet wallet integration
- Optional Streamable HTTP MCP server scaffold
- Soroban treasury contract source scaffold
Run locally with XAMPP
Requirements
- Windows, macOS or Linux
- XAMPP or another PHP 8.1+ and MySQL/MariaDB environment
- A modern browser
- PHP PDO MySQL extension enabled
- Optional: Node.js for the separate MCP server
- Optional: an OpenAI API key for AI-powered reasoning
Setup
1. Clone or download this repository.
2. Copy the application folder into your web server's document root.
   For XAMPP on Windows, this is commonly C:\xampp\htdocs\.
3. Start Apache and MySQL.
4. Open phpMyAdmin and create/configure the database using the SQL file
   in database/.
5. Back up any existing Thumela database before importing. Inspect
   the SQL file first: the provided setup script may drop and recreate
   the thumela database.
6. Configure the database connection using the settings in
   backend/config/database.php or the supported server environment
   variables.
7. If enabling the AI assistant, configure OPENAI_API_KEY in the
   server/Apache environment. Never place the key in frontend
   JavaScript, HTML or a public GitHub repository.
8. Open the application through Apache, for example:
   http://localhost/Thumela_PHP_All_Features/.
9. Register a demo account and use synthetic data only.
For detailed instructions, see INSTALLING_THUMELA.md,
AI_AGENT_SETUP.md and FEATURES_AND_LIMITATIONS.md.
Mobile access
Thumela includes a PWA manifest and service worker. For installation on
a phone, deploy the application over HTTPS, then use the browser's
Install app or Add to Home Screen option where available.

The PWA uses the hosted PHP backend for authenticated and AI features,
so those functions still need a working internet connection. A Capacitor
Android packaging guide is included, but a production-ready APK has not
been built and device-tested in this repository.
Agentic Finance and MCP work
The project includes: - An authenticated, local MCP-style JSON-RPC
endpoint in the PHP application. - An optional remote Streamable HTTP
MCP server scaffold for read-only Stellar Testnet account and
transaction information, plus payment preparation. - A Soroban treasury
smart-contract source scaffold with an administrator authorisation
boundary, pause control and daily spending limit.

These are development components, not a deployed production finance
infrastructure. The remote MCP server has not been deployed and
connected to a hosted environment. The Soroban contract has not been
compiled, audited, deployed or connected to the application.
Important limitations and safety
Thumela is a hackathon prototype, not a live banking or remittance
service.
- Demo balances, recipients, savings activity, bills and PHP payment
  records are simulated.
- No bank, mobile-money, remittance-provider or Mukuru integration is
  included.
- No real fiat money is transferred or held by the application.
- Stellar functionality is limited to Testnet XLM. Testnet assets have
  no real-world monetary value; wallet signing and transfers must be
  tested manually.
- The AI can retrieve information and prepare a quote, but it cannot
  approve a PHP demo payment.
- The remote MCP server and Soroban contract are scaffolds and are not
  deployed production services.
- A standalone Android APK and native mobile-wallet handoff still
  require implementation and device testing.
- Accessibility testing with blind and low-vision users, including
  TalkBack and other assistive technologies, is still required.
- The project has not undergone an independent security audit or
  production compliance review.
Never enter real banking passwords, card details, PINs, OTPs, wallet
recovery phrases or private keys into the demo. Do not use demo receipts
as proof that money has settled.
Project structure
Thumela_PHP_All_Features/
├── index.html
├── backend/
│   ├── api/
│   ├── config/
│   └── services/
├── database/
├── assets/
├── mcp-server/
├── soroban/
│   └── treasury/
├── HACKATHON_SUBMISSION/
├── manifest.webmanifest
├── service-worker.js
└── README.md
Roadmap
- Complete end-to-end testing of the voice and assistant experience on
  supported devices.
- Conduct accessibility testing with blind and low-vision users.
- Deploy and secure the remote MCP service.
- Compile, test, review and deploy the Soroban treasury contract on
  Testnet.
- Complete native wallet handoff and Android APK build/testing.
- Evaluate regulated payment-provider integrations, subject to access,
  compliance and security requirements.
Contributing
Issues, feedback and contributions are welcome. Please avoid submitting
secrets, personal financial information, wallet credentials or private
keys. Review SECURITY.md before reporting a security concern.
Acknowledgement
Built for the W3Node / Node Engine 2026 Agentic Finance track.
Thumela --- accessible finance should begin with the person, not the
screen.

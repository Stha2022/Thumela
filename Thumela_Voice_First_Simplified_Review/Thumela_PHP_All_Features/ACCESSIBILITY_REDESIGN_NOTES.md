# Thumela accessibility-focused update

This update reorganises the existing PHP/MySQL Thumela prototype into simpler task pages:

- Home: wallet balance only, with a read-balance action.
- Send money: a dedicated recipient and amount page; the request is passed to the existing agent flow for review.
- Receive money: payment request page.
- Trusted people: recipient management page.
- Transactions: transaction history page.
- Accessibility: language and voice settings.

The voice assistant is available across the signed-in experience. The large microphone button is the primary action; typing remains an optional fallback.

## Important accessibility and safety notes

- Browser speech recognition and speech synthesis depend on browser, operating-system and installed language support. A language option does not install a voice.
- The app continues to require an explicit payment approval. The existing build is a demo and does not move real money.
- Standard web apps cannot reliably intercept Android power or volume buttons. Do not remap these buttons in the browser. For hardware-key integration, evaluate a native Android accessibility service or supported headset/media controls, with explicit user consent and Android permission review.
- Test with TalkBack on a real Android device and with blind/low-vision participants. Check focus order, screen-reader labels, touch target sizes, spoken confirmations and stop-speaking behaviour.

## Local run

Keep the folder structure intact. Run the PHP project under XAMPP's `htdocs`, start Apache and MySQL, import `database/thumela.sql` in phpMyAdmin, and open the project through `http://localhost/.../index.html`.

This ZIP is a source update. It is not a compiled Android APK.


## Voice-first simplification review (13 Nov 2026)
- Home is limited to the wallet balance and the persistent voice companion.
- Savings goals, bill/airtime tools and split-expense calculator are excluded from the primary interface.
- The six primary destinations remain Home, Send money, Receive money, Trusted people, Transactions and Accessibility.
- When a user has selected voice-first mode, touching a navigation destination announces its title and short purpose. Spoken navigation commands are restricted to these supported destinations.
- Speech output and recognition use browser/device speech capabilities; availability and African-language coverage vary by Android/browser. Native Android speech recognition still needs device testing or a dedicated native speech plugin.

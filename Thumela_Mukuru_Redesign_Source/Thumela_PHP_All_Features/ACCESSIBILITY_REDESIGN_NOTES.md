# Thumela — Voice-first accessibility redesign

## Product focus
Thumela is a South African money-transfer demonstration designed specifically for blind and low-vision people. It is not a general-purpose finance dashboard.

## Simplified navigation
- Home: demo balance and voice assistant.
- Send money: select a saved recipient, enter ZAR amount, hear fee and total, review payout, explicitly confirm or cancel.
- My people: add and hear trusted recipient details.
- Transfer history: review simulated transfers.
- Accessibility: choose a supported spoken language, text size and stronger contrast.

## Accessibility
- Large touch targets, generous spacing and high-contrast controls.
- Semantic headings, labels, live status announcements and navigation landmarks.
- Speech output with a visible Stop voice control.
- Speech input where supported by browser/device.
- No voice-only password entry or voiceprint authentication.
- Enable Android TalkBack for best screen-reader support.
- Speech recognition and local language voices depend on device/browser support and installed language packs. Language selection does not install a voice or guarantee recognition.

## Demo transaction rules
- Initial local demo balance: R2,000.
- Fee simulation: 1.5% of transfer amount, minimum R2.
- Fee, total deduction and recipient payout are displayed before confirmation.
- Confirming deducts amount plus fee from local demo balance and records a simulated completed transfer.
- Recipient payout is the entered amount in the simulation.
- No real payment, bank transfer, wallet settlement or remittance API occurs.
- Demo data is stored locally on the device/browser.

## Launch scope
- South Africa only; currency ZAR.
- Remittance partner undecided.
- Mama Money, Mukuru and Hello Paisa are possible future partners only; no partnership or API integration is claimed.

## Before real-money launch
Conduct usability testing with blind and low-vision participants; validate TalkBack and speech support; select and contract a licensed transfer provider; obtain API access; complete legal, regulatory, privacy, security, KYC/AML and fraud reviews; and replace local demo storage with a secure production backend.


### Entry screens restored
The app now starts with the Thumela intro GIF and a welcome screen, followed by accessible Log in / Create an account choices. The forms provide labels, keyboard focus, and spoken guidance. Authentication is simulated locally and does not provide real account security.

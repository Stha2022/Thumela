# Thumela redesign — Mukuru preference and rand speech

## What changed
- Send Money now identifies Mukuru as the preferred remittance partner.
- The review step clarifies that Mukuru is not contacted and no payout is initiated.
- The global speech output converts displayed ZAR currency figures into spoken
  rand and cents, e.g. R 2,000.00 becomes “two thousand rand”.
- The existing voice-first and low-vision interface and simulated transfer ledger
  remain in place.

## Safety and integration boundary
Mukuru is a preferred-provider selection for this prototype only. This build does
not claim a partnership, does not use Mukuru branding as an endorsement, and does
not connect to a Mukuru service. A production integration must be authorised by
Mukuru and implemented against its approved API and commercial terms.

## Speech behavior
The app converts rand amounts in the assistant's spoken text into English words.
Actual voice quality and South African accent depend on the voices installed on
the Android device. The user can stop speech using the existing Stop voice control.

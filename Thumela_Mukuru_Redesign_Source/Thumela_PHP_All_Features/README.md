# Thumela — Voice-first demonstration

A simplified South African money-transfer demonstration designed for blind and low-vision people.

Open `index.html` in a browser to try the demo. It includes a simulated ZAR balance, trusted people, transfer quote/review/confirmation, transaction history, speech output and speech recognition where supported.

**No real money is sent.** The app has no remittance-provider integration. The partner is undecided.

See `ACCESSIBILITY_REDESIGN_NOTES.md` for design scope and `ANDROID_APK_GUIDE.txt` for steps to compile an Android debug APK with Android Studio.


## Welcome and demo account flow
The app opens on a welcome screen with the Thumela intro GIF, then offers Log in or Create an account before showing the dashboard. Both are simulated local demo flows: any valid email and a password of at least four characters can be used to log in, and registration stores a simple demo profile on the device. No real user account or financial account is created.

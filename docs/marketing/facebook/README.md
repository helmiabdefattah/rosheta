# Facebook page kit — mostashfaOn

Ready-to-upload artwork and copy for the mostashfaOn Facebook page, in English and Arabic.

| File | What it is |
| --- | --- |
| [`page-bio.md`](page-bio.md) | Page name, username, category, bio, About text, services, CTA — EN + AR |
| [`post-captions.md`](post-captions.md) | Caption for every post image (EN + AR), hashtags and a 2-week posting plan |
| `images/` | 30 PNGs — profile pictures, covers (with and without the phone number), 11 post designs × 2 languages, and a contact card |
| `source/` | The generator used to build the images, so the text can be edited and re-rendered |

## Images

All artwork uses the existing brand: the `mo-logo` shield, the purple → teal gradient
(`#4A2C6B` → `#3E5F9B` → `#2FA8B4`) sampled from the logo, Poppins/Inter for English and
Cairo/Tajawal for Arabic. Every image carries `dev.mostashfaon.com`.

**Profile picture** (1000 × 1000, Facebook crops it to a circle)

- `profile-picture.png` — shield + "mostashfaOn / connecting health"
- `profile-picture-mark-only.png` — shield only; **recommended**, it stays readable at the small
  size Facebook shows next to posts and comments

**Cover photo** (1640 × 856, the size Facebook asks for)

- `cover-en.png`, `cover-ar.png` — all text sits in the centre safe area, so nothing is lost when
  Facebook crops the cover on mobile or hides the bottom-left corner behind the profile picture
- `cover-en-phone.png`, `cover-ar-phone.png` — the same covers with **01070711504** (call and
  WhatsApp) on a pill under the trial link

**Posts** (1080 × 1080 square — best reach in the feed). Each theme exists as `-en` and `-ar`:

| # | File | Theme |
| --- | --- | --- |
| 01 | `post-01-all-in-one-{en,ar}.png` | The whole clinic in one place |
| 02 | `post-02-free-trial-{en,ar}.png` | Free trial on dev.mostashfaon.com |
| 03 | `post-03-appointments-{en,ar}.png` | Reservations and booking requests |
| 04 | `post-04-notifications-{en,ar}.png` | Push notifications and reminders |
| 05 | `post-05-apps-{en,ar}.png` | Clinic app + patient app |
| 06 | `post-06-prescription-{en,ar}.png` | Electronic prescriptions |
| 07 | `post-07-invoices-{en,ar}.png` | Invoices, collections and reports |
| 08 | `post-08-waiting-screen-{en,ar}.png` | Waiting-area display screen |
| 09 | `post-09-reception-{en,ar}.png` | Reception app and self check-in |
| 10 | `post-10-medical-history-{en,ar}.png` | Patient file and medical history |
| 11 | `post-11-website-{en,ar}.png` | Doctor profile website + features on demand |
| — | `contact-{en,ar}.png` | Contact card: **01070711504** for calls and WhatsApp (`wa.me/201070711504`) |

## Editing the text and re-rendering

`source/content.cjs` holds every line that appears on the images; `source/gen.cjs` builds the HTML
and the design system. (They are `.cjs` because the repository's `package.json` sets
`"type": "module"`, and these are CommonJS scripts.) Rendering needs Chromium and the four Google fonts (Poppins, Inter, Cairo,
Tajawal) as local `.woff2` files next to the HTML, plus a copy of `public/images/mo-logo.png`:

```bash
# 1. put fonts + mo-logo.png in a build dir, edit the OUT path at the top of gen.js
node gen.cjs <build-dir>          # writes one HTML file per image

# 2. screenshot each one at its exact size (headless_shell honours --window-size precisely)
headless_shell --no-sandbox --hide-scrollbars --allow-file-access-from-files \
  --force-device-scale-factor=1 --window-size=1080,1080 --virtual-time-budget=6000 \
  --screenshot=out/post-01-all-in-one-en.png file://$PWD/post-01-all-in-one-en.html
```

Use `--window-size=1000,1000` for the profile pictures and `--window-size=1640,856` for the covers.
The page shrinks its own headline if new copy overflows, so longer text stays inside the frame.

#!/usr/bin/env python3
"""
Generates a WordPress import file (WXR) for GoldStandardX Gaming that:
  - Adds one "server hub" page per community (per-server Rules / Events / Updates / Info),
    children of "Our Servers" so they live at /our-servers/<slug>/ and stay OUT of the top nav.
  - Replaces the "Our Servers" page so every community card gets an "Open Server Page" button
    (and an "Official Website" button where a real external site exists).
  - Replaces the "Join Staff" page, adding an editable Leadership & Contacts block with
    owner + head-admin email spots, while keeping the migrated old-staff-site content.

Run:  python3 generate_import.py  ->  writes goldstandardx-gaming-update.xml
"""

SITE = "https://yellowgreen-jay-473493.hostingersite.com"
UPLOADS = SITE + "/wp-content/uploads/2026/06"
DATE = "2026-06-26 12:00:00"

# Shared GSXG look (taken from the live pages so new pages match the existing design),
# plus a small readability pass and per-server helper classes.
STYLE = """<style>
.gsxg-wrap{--bg:#070b12;--panel:#111826;--panel2:#171f2e;--line:rgba(255,255,255,.12);--gold:#d6b15d;--red:#a82331;--text:#f7f2df;--muted:#b9c0cc;background:#070b12;color:var(--text);font-family:Inter,Arial,sans-serif;line-height:1.6;margin:0}.gsxg-wrap *{box-sizing:border-box}.gsxg-section{padding:72px 22px}.gsxg-inner{max-width:1180px;margin:0 auto}.gsxg-hero{min-height:460px;display:flex;align-items:center;background:radial-gradient(circle at 18% 20%,rgba(214,177,93,.2),transparent 28%),radial-gradient(circle at 80% 20%,rgba(168,35,49,.2),transparent 30%),linear-gradient(135deg,#070b12 0%,#101827 52%,#22080d 100%);position:relative;overflow:hidden}.gsxg-kicker{color:var(--gold);font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:.82rem}.gsxg-title{font-size:clamp(2.4rem,6vw,5.6rem);line-height:.92;margin:14px 0 18px;text-transform:uppercase;max-width:920px}.gsxg-lead{font-size:clamp(1.05rem,2vw,1.3rem);color:#d7dce5;max-width:840px}.gsxg-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:26px}.gsxg-btn{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:12px 18px;border-radius:4px;text-decoration:none!important;font-weight:900;text-transform:uppercase;letter-spacing:.03em;border:1px solid var(--line);color:var(--text)!important}.gsxg-btn.primary{background:linear-gradient(135deg,var(--gold),#9e762e);color:#080b10!important;border:0}.gsxg-btn.dark{background:#151d2a}.gsxg-btn.red{background:linear-gradient(135deg,var(--red),#67111a);border:0;color:#fff!important}.gsxg-btn.ghost{background:transparent}.gsxg-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:24px}.gsxg-heading h2{font-size:clamp(2rem,4vw,3.4rem);line-height:1;margin:4px 0 0;text-transform:uppercase}.gsxg-heading p,.gsxg-muted{color:var(--muted);margin:0;max-width:760px}.gsxg-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.gsxg-card{background:linear-gradient(180deg,var(--panel),var(--panel2));border:1px solid var(--line);border-radius:6px;padding:22px;min-height:200px;display:flex;flex-direction:column}.gsxg-card h3{margin:0 0 6px;font-size:1.22rem;text-transform:uppercase}.gsxg-tag{display:inline-flex;color:#070b12;background:var(--gold);font-size:.72rem;font-weight:900;padding:4px 8px;border-radius:3px;text-transform:uppercase;margin-bottom:14px;width:max-content}.gsxg-card p{color:#cbd2dc}.gsxg-card .gsxg-actions{margin-top:auto}.gsxg-logo-slot{height:150px;border:1px solid rgba(214,177,93,.24);background:radial-gradient(circle at 50% 20%,rgba(214,177,93,.16),transparent 40%),rgba(0,0,0,.24);border-radius:6px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;overflow:hidden}.gsxg-logo-slot img{max-width:100%;max-height:140px;object-fit:contain;display:block}.gsxg-logo-placeholder{font-weight:900;text-transform:uppercase;color:rgba(247,242,223,.62);letter-spacing:.08em;text-align:center;font-size:.9rem;padding:12px}.gsxg-list{margin:14px 0 0;padding-left:20px;color:#d7dce5}.gsxg-list li{margin:7px 0}.gsxg-split{display:grid;grid-template-columns:1fr 1fr;gap:18px}.gsxg-band{background:linear-gradient(135deg,#111826,#22080d);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}.gsxg-footer-cta{text-align:center}.gsxg-footer-cta h2{font-size:clamp(2rem,4vw,4.2rem);line-height:1;margin:0 0 14px;text-transform:uppercase}.gsxg-note{font-size:.92rem;color:#9fb0c4;border-left:3px solid var(--gold);padding:6px 0 6px 12px;margin:14px 0}@media(max-width:900px){.gsxg-grid,.gsxg-split{grid-template-columns:1fr}.gsxg-heading{display:block}}
/* GSXG readability pass */
body, .site, .content-bg, .entry-content { background:#070b12 !important; }
.site-footer, footer.site-footer, .footer-html, .powered-by, .site-info { display:none !important; }
.gsxg-wrap{font-size:18px;-webkit-font-smoothing:antialiased;}
.gsxg-lead{color:#f3f6fb!important;font-weight:500;text-shadow:0 1px 1px rgba(0,0,0,.32);}
.gsxg-card{background:linear-gradient(180deg,#151f30,#0f1725)!important;border-color:rgba(255,255,255,.2)!important;box-shadow:0 18px 44px rgba(0,0,0,.28);}
.gsxg-card p,.gsxg-list,.gsxg-card li{color:#edf2fa!important;font-size:1.02rem;}
.gsxg-heading p,.gsxg-muted{color:#e5ebf4!important;font-size:1.04rem;}
.gsxg-card h3,.gsxg-heading h2,.gsxg-title{color:#fff7e2!important;text-shadow:0 2px 12px rgba(0,0,0,.32);}
.gsxg-tag{background:#f1c96d!important;color:#070b12!important;}
.gsxg-readable{background:linear-gradient(180deg,#121b2a,#0b111d);border:1px solid rgba(255,255,255,.16);border-radius:6px;padding:24px;margin-top:18px;}
.gsxg-readable h3{margin-top:0;color:#fff7e2;text-transform:uppercase;}
.gsxg-readable p,.gsxg-readable li{color:#edf2fa;font-size:1.04rem;line-height:1.72;}
.gsxg-readable strong{color:#f1c96d;}
@media(max-width:900px){.gsxg-wrap{font-size:17px}.gsxg-section{padding:54px 18px!important}}</style>"""

# --- Per-category rule / event text (fully editable later in WordPress) ---------------
RULES = {
    "pvp": [
        "Cheating, scripting, macros, and third-party exploits = permanent ban.",
        "Raiding and PvP are allowed within the wipe ruleset — no off-server harassment.",
        "Group / team size limit is set per wipe. Check the current wipe rules in Discord.",
        "No blocking monuments, spawns, or roads to deny other players.",
        "No racism, hate speech, threats, or doxxing in any GSXG channel.",
        "Bug or exploit found? Report it privately to staff — do not abuse it.",
    ],
    "pve": [
        "This is a PvE community — no raiding or killing other players outside event/arena zones.",
        "No griefing: do not block, wall-in, or build on top of another player's base or monuments.",
        "Keep build sizes reasonable and leave space for others to settle.",
        "KOS (kill on sight) is only allowed inside clearly marked event or arena areas.",
        "Be welcoming — Hornet Heaven is a relaxed, friendly survival server.",
        "No cheating, duping, or exploiting. Report bugs to staff.",
    ],
    "survival": [
        "No cheating, duping, exploiting, or use of unauthorized mods.",
        "Respect build areas — no excessive foundation/pillar spam to claim land.",
        "PvP and raid rules follow this server's current settings (see Discord).",
        "No offensive player names, clan tags, or in-game structures.",
        "Settle disputes through staff tickets, not chat arguments.",
        "No harassment, hate speech, or threats toward players or staff.",
    ],
    "rp": [
        "Stay in character inside RP zones — no breaking immersion in active scenes.",
        "No RDM (random deathmatch) or VDM (vehicle deathmatch).",
        "No metagaming or powergaming — keep roleplay fair and realistic.",
        "Follow the New Life Rule (NLR) after a character death.",
        "Respect emergency services, jobs, and the in-city economy systems.",
        "Use OOC (out-of-character) channels for non-RP talk.",
    ],
    "hub": [
        "Be respectful to every member across all GoldStandardX communities.",
        "No spam, unsolicited advertising, or DM scams.",
        "Follow Discord's Terms of Service and each server's own rules.",
        "Use the correct channels and listen to staff direction.",
        "No racism, hate speech, harassment, or NSFW content.",
        "Open a support ticket for help, reports, or appeals.",
    ],
    "other": [
        "Standard GoldStandardX conduct applies — respect players and staff.",
        "No cheating, exploiting, or disruptive behaviour.",
        "Full server-specific rules are added here when the server launches.",
        "No racism, hate speech, harassment, or threats.",
        "Use support tickets for questions, reports, and appeals.",
    ],
}

EVENTS = {
    "pvp": [
        ("Wipe Day", "Map + blueprint wipe schedule. Add the date, time, and new map seed here."),
        ("Raid Weekend", "Boosted weekend events, raid challenges, and PvP prize pools."),
        ("Clan Tournament", "Organised team vs team competition with rewards. Add the bracket and date."),
    ],
    "pve": [
        ("Community Build", "Group build projects and base showcases. Add the theme and date."),
        ("Boss / Event Arena", "Scheduled PvE boss fights or arena nights in the event zone."),
        ("Giveaway Night", "Community giveaways and loot drops for active members."),
    ],
    "survival": [
        ("Wipe / Season Launch", "New map or season start. Add the date, time, and what changes."),
        ("Boss Run / Raid Boss", "Group boss fight or raid event. Add the time and meeting point."),
        ("Build Contest", "Themed building competition with community voting and prizes."),
    ],
    "rp": [
        ("City Event", "Server-wide RP event — festival, election, heist, or city crisis."),
        ("Job Spotlight", "Featured department or business RP day with bonuses."),
        ("Community Meet", "Casual in-city gathering or OOC community night."),
    ],
    "hub": [
        ("Network Game Night", "Cross-server community night. Add the game and time."),
        ("Seasonal Event", "Holiday or seasonal network event across multiple servers."),
        ("Giveaway / Drop", "Network-wide giveaways for active community members."),
    ],
    "other": [
        ("Launch Event", "Opening event for this server. Add the date and details."),
        ("Community Night", "Casual scheduled play session for members."),
        ("Giveaway", "Add giveaway details, prizes, and how to enter."),
    ],
}

# --- Community data ------------------------------------------------------------------
# (name, slug, tag, category, discord, website-or-None, logo-file-or-None, short-code, description)
SERVERS = [
    ("GoldStandardX Community Hub", "community-hub", "Community", "hub",
     "https://discord.gg/4xEgrdQbFG", "https://sites.google.com/view/goldstandardx-gaming", None, "GSXG",
     "The main GSXG Discord for announcements, cross-server community, support, events, staff applications, partner applications, and network-wide updates."),
    ("Final Order", "final-order", "Rust • PvP", "pvp",
     "https://discord.gg/7Vs2CJ8P7n", "https://www.rustfinalorder.org/home", None, "FO",
     "A full Rust PvP ecosystem built for builders, grinders, raiders, and long-term community survival."),
    ("Final Order Console Edition", "final-order-console", "Rust Console", "pvp",
     "https://discord.gg/7Vs2CJ8P7n", "https://www.rustfinalorder.org/home", "final-order-console-edition.png", "FO",
     "Final Order for Rust Console Edition players, built around the same GSXG standard for fair play, support, events, and community growth."),
    ("Ark of War", "ark-of-war", "ARK", "survival",
     "https://discord.gg/kQQ6DxTcdr", None, None, "AOW",
     "A long-term ARK community focused on taming, building, progression, boss runs, and coordinated survival."),
    ("Last Breath Survival", "last-breath-survival", "DayZ / 7 Days", "survival",
     "https://discord.gg/Cq8fRnPxjm", None, None, "LBS",
     "Apocalypse survival for players who want danger, progression, teamwork, and a home across DayZ and 7 Days to Die."),
    ("Warborn Exiles", "warborn-exiles", "Conan", "survival",
     "https://discord.gg/V5A3SJDPt3", None, None, "WE",
     "A Conan Exiles community built around clans, building, conflict, rules, and a strong admin standard."),
    ("EmberCraft Survival", "embercraft-survival", "Minecraft", "survival",
     "https://discord.gg/AsAWq2DTXU", None, None, "ECS",
     "Minecraft survival for players who enjoy building, economy, progression, community projects, and seasonal updates."),
    ("Emberveil Kingdom", "emberveil-kingdom", "Enshrouded", "survival",
     "https://discord.gg/uc7nUATXn9", None, None, "EK",
     "A fantasy survival community focused on exploration, crafting, building, cooperative progression, and realm growth."),
    ("Convict Outbreak", "convict-outbreak", "SCUM", "survival",
     "https://discord.gg/FYWbQXmV54", None, None, "CO",
     "Hardcore survival, looting, base building, and player-driven stories in the SCUM universe."),
    ("Prospectors Survival", "prospectors-survival", "Icarus", "survival",
     "https://discord.gg/nTggx7u9zB", None, None, "PS",
     "Mission-focused survival for players who like exploration, resource planning, crafting, and squad progression."),
    ("Windrose Frontier Network", "windrose-frontier-network", "Frontier Network", "other",
     "https://discord.gg/Yu4B2mdyhQ", None, "windrose-frontier-network.png", "WFN",
     "A frontier-themed community built for exploration, survival, roleplay potential, and future GSXG expansion."),
    ("MetroLife RP", "metrolife-rp", "GTA V Roleplay", "rp",
     "https://discord.gg/Yu4B2mdyhQ", None, "metrolife-rp.png", "ML",
     "A GTA V roleplay community centered on city life, emergency services, economy, character stories, and organized RP."),
    ("Gaming Server", "gaming-server", "Other Games", "other",
     "https://discord.gg/Yu4B2mdyhQ", None, None, "GS",
     "A flexible home for new games, test communities, events, future servers, and network experiments."),
    ("Fallen Bloodline", "fallen-bloodline", "Soulmask", "survival",
     "https://discord.gg/38WjMttgad", None, None, "FB",
     "Tribe survival, crafting, exploration, and group progression for Soulmask players."),
    ("Hornet Heaven", "hornet-heaven", "Rust PvE", "pve",
     "https://discord.gg/bFsCT43hbY", None, None, "HH",
     "Custom Rust PvE with quality-of-life features, community progression, relaxed survival, and custom content."),
]

OUR_SERVERS_ID = 11
STAFF_ID = 13
FIRST_HUB_ID = 101


def logo_block(logo_file, short):
    if logo_file:
        return ('<div class="gsxg-logo-slot"><img src="%s/%s" alt="%s logo" loading="lazy"></div>'
                % (UPLOADS, logo_file, short))
    return ('<div class="gsxg-logo-slot"><span class="gsxg-logo-placeholder">%s<br>Logo Slot</span></div>'
            % short)


def li(items):
    return "".join("<li>%s</li>" % x for x in items)


def hub_page(s, pid):
    name, slug, tag, cat, discord, website, logo, short, desc = s
    rules = RULES[cat]
    events = EVENTS[cat]
    website_btn = ('<a class="gsxg-btn red" href="%s" target="_blank" rel="noopener">Official Website</a>' % website) if website else ""
    website_info = ('<strong>Official Website:</strong> <a href="%s" target="_blank" rel="noopener" style="color:#f1c96d">%s</a><br>' % (website, website)) if website \
        else '<strong>Official Website:</strong> not set yet — edit this page to add the link.<br>'

    event_cards = "".join(
        '<div class="gsxg-card"><span class="gsxg-tag">%s</span><h3>%s</h3><p>%s</p></div>' % (lbl, lbl, body)
        for lbl, body in events
    )

    content = (
        STYLE +
        '<div class="gsxg-wrap">' +

        # HERO
        '<section class="gsxg-section gsxg-hero"><div class="gsxg-inner">' +
        '<div class="gsxg-kicker">GoldStandardX Gaming &bull; %s</div>' % tag +
        '<h1 class="gsxg-title">%s</h1>' % name +
        '<p class="gsxg-lead">%s</p>' % desc +
        '<div class="gsxg-actions">' +
        '<a class="gsxg-btn primary" href="%s" target="_blank" rel="noopener">Join Discord</a>' % discord +
        website_btn +
        '<a class="gsxg-btn dark" href="%s/our-servers/">Back to Our Servers</a>' % SITE +
        '</div></div></section>' +

        # LOGO + QUICK LINKS
        '<section class="gsxg-section"><div class="gsxg-inner gsxg-split">' +
        '<div class="gsxg-card">' + logo_block(logo, short) +
        '<span class="gsxg-tag">%s</span><h3>%s</h3><p>%s</p></div>' % (tag, name, desc) +
        '<div class="gsxg-card"><span class="gsxg-tag">Quick Links</span><h3>%s Pages</h3>' % name +
        '<p>Everything for this community lives on this page. Jump to a section:</p>' +
        '<div class="gsxg-actions">' +
        '<a class="gsxg-btn dark" href="#rules">Rules</a>' +
        '<a class="gsxg-btn dark" href="#events">Events</a>' +
        '<a class="gsxg-btn dark" href="#updates">Updates</a>' +
        '<a class="gsxg-btn dark" href="#info">Server Info</a>' +
        '</div></div></div></section>' +

        # RULES (per server)
        '<section id="rules" class="gsxg-section gsxg-band"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">%s</div><h2>Server Rules</h2></div>' % name +
        '<p>These rules apply to %s only. Edit them in WordPress to match your exact server policies.</p></div>' % name +
        '<div class="gsxg-readable"><h3>%s Rules</h3><ul class="gsxg-list">%s</ul></div>' % (name, li(rules)) +
        '</div></section>' +

        # EVENTS (per server)
        '<section id="events" class="gsxg-section"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">%s</div><h2>Events</h2></div>' % name +
        '<p>Events for %s. Replace these cards with real dates, times, prizes, and Discord event links.</p></div>' % name +
        '<div class="gsxg-grid">' + event_cards + '</div>' +
        '</div></section>' +

        # UPDATES (per server)
        '<section id="updates" class="gsxg-section gsxg-band"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">%s</div><h2>Updates & Patch Notes</h2></div>' % name +
        '<p>Post wipe notices, patch notes, mod/plugin changes, and announcements for %s here.</p></div>' % name +
        '<div class="gsxg-grid">' +
        '<div class="gsxg-card"><span class="gsxg-tag">Latest</span><h3>Newest Update</h3><p>Add your most recent change or announcement here.</p></div>' +
        '<div class="gsxg-card"><span class="gsxg-tag">Wipe / Season</span><h3>Schedule</h3><p>Add the current wipe or season schedule for this server.</p></div>' +
        '<div class="gsxg-card"><span class="gsxg-tag">Roadmap</span><h3>Coming Soon</h3><p>Upcoming plans, features, or events for this community.</p></div>' +
        '</div></div></section>' +

        # INFO
        '<section id="info" class="gsxg-section"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">%s</div><h2>Server Info</h2></div>' % name +
        '<p>Connection details and links for this community. Edit anything in WordPress.</p></div>' +
        '<div class="gsxg-readable"><p>' +
        '<strong>Community:</strong> %s<br>' % name +
        '<strong>Type:</strong> %s<br>' % tag +
        '<strong>Discord:</strong> <a href="%s" target="_blank" rel="noopener" style="color:#f1c96d">%s</a><br>' % (discord, discord) +
        website_info +
        '<strong>Connect / IP:</strong> add server IP or join command here.<br>' +
        '<strong>Wipe / Reset:</strong> add the wipe or reset schedule here.</p>' +
        '<p class="gsxg-note">Edit this page: WordPress &rarr; Pages &rarr; "%s" &rarr; change the text &rarr; Update.</p>' % name +
        '</div></div></section>' +

        '</div>'
    )
    return page_item(name, slug, pid, content, parent=OUR_SERVERS_ID, menu_order=0)


def our_servers_page():
    """Rebuilt Our Servers grid: each card gets an Open Server Page button (+ website where known)."""
    cards = []
    for i, s in enumerate(SERVERS):
        name, slug, tag, cat, discord, website, logo, short, desc = s
        site_btn = ('<a class="gsxg-btn red" href="%s" target="_blank" rel="noopener">Website</a>' % website) if website else ""
        card = (
            '<div class="gsxg-card">' + logo_block(logo, short) +
            '<span class="gsxg-tag">%s</span><h3>%s</h3><p>%s</p>' % (tag, name, desc) +
            '<div class="gsxg-actions">' +
            '<a class="gsxg-btn primary" href="%s/our-servers/%s/">Open Server Page</a>' % (SITE, slug) +
            '<a class="gsxg-btn dark" href="%s" target="_blank" rel="noopener">Join Discord</a>' % discord +
            site_btn +
            '</div></div>'
        )
        cards.append(card)
    grid = "\n".join(cards)

    content = (
        STYLE +
        '<div class="gsxg-wrap">' +
        '<section class="gsxg-section gsxg-hero"><div class="gsxg-inner">' +
        '<div class="gsxg-kicker">GoldStandardX Gaming</div><h1 class="gsxg-title">Our Servers</h1>' +
        '<p class="gsxg-lead">Browse every GSXG community. Each server now has its own page with its own rules, events, updates, and info &mdash; because PvE, PvP, and roleplay all play by different rules.</p>' +
        '<div class="gsxg-actions">' +
        '<a class="gsxg-btn primary" href="https://discord.gg/4xEgrdQbFG" target="_blank" rel="noopener">Main Community Discord</a>' +
        '<a class="gsxg-btn dark" href="%s/">Back Home</a></div></div></section>' % SITE +

        '<section class="gsxg-section gsxg-band"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">How It Works</div><h2>Rules & Events Are Per Server</h2></div>' +
        '<p>Click <strong>Open Server Page</strong> on any community below. Each server page has that community&rsquo;s own rules, events, updates, and links &mdash; so Hornet Heaven (PvE) and Final Order (PvP) each keep their own rules instead of one shared list.</p></div>' +
        '</div></section>' +

        '<section class="gsxg-section"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">Server Network</div><h2>Active Communities</h2></div>' +
        '<p>Every community has its own logo spot and its own server page. Logos show when artwork is uploaded; reserved logo slots stay in place until the rest of the artwork is added.</p></div>' +
        '<div class="gsxg-grid">' + grid + '</div>' +
        '</div></section>' +
        '</div>'
    )
    return page_item("Our Servers", "our-servers", OUR_SERVERS_ID, content, parent=0, menu_order=0)


def staff_page():
    content = (
        STYLE +
        '<div class="gsxg-wrap">' +
        '<section class="gsxg-section gsxg-hero"><div class="gsxg-inner">' +
        '<div class="gsxg-kicker">GoldStandardX Gaming</div><h1 class="gsxg-title">Join Staff</h1>' +
        '<p class="gsxg-lead">Help operate a growing multi-game network with professionalism, consistency, and respect for the players who make the community active.</p>' +
        '<div class="gsxg-actions">' +
        '<a class="gsxg-btn primary" href="https://form.jotform.com/261488914584168" target="_blank" rel="noopener">Sign Staff Application</a>' +
        '<a class="gsxg-btn dark" href="%s/">Back to Main Site</a></div></div></section>' % SITE +

        # Leadership & Contacts — EDITABLE EMAIL SPOTS
        '<section class="gsxg-section"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">Contacts</div><h2>Leadership & Contacts</h2></div>' +
        '<p>Reach the people who run GSXG. Replace the email addresses below with the real ones (edit this page in WordPress).</p></div>' +
        '<div class="gsxg-grid">' +
        '<div class="gsxg-readable"><h3>Owner</h3><p><strong>Tobbie330</strong><br>' +
        'Email: <a href="mailto:owner@goldstandardxgaming.org" style="color:#f1c96d">owner@goldstandardxgaming.org</a><br>' +
        '<span class="gsxg-note">Edit: replace with the owner&rsquo;s real email.</span></p></div>' +
        '<div class="gsxg-readable"><h3>Head Admin</h3><p><strong>Chuck &bull; TK &bull; Chad</strong><br>' +
        'Email: <a href="mailto:headadmin@goldstandardxgaming.org" style="color:#f1c96d">headadmin@goldstandardxgaming.org</a><br>' +
        '<span class="gsxg-note">Edit: add each head admin&rsquo;s real email here.</span></p></div>' +
        '<div class="gsxg-readable"><h3>General Staff Contact</h3><p>For staff questions and applications.<br>' +
        'Email: <a href="mailto:staff@goldstandardxgaming.org" style="color:#f1c96d">staff@goldstandardxgaming.org</a><br>' +
        'Or open a <a href="https://ops.goldstandardxgaming.org/view/gsx-community-bot" target="_blank" rel="noopener" style="color:#f1c96d">support ticket</a>.</p></div>' +
        '</div></div></section>' +

        # What staff do / expectations
        '<section class="gsxg-section gsxg-band"><div class="gsxg-inner gsxg-split">' +
        '<div class="gsxg-card"><span class="gsxg-tag">What Staff Do</span><h3>Support the Community</h3><ul class="gsxg-list">' +
        '<li>Answer player questions and guide new members.</li><li>Help enforce rules fairly and consistently.</li>' +
        '<li>Assist with tickets, events, server feedback, and community communication.</li>' +
        '<li>Represent GSXG with maturity in-game and on Discord.</li></ul></div>' +
        '<div class="gsxg-card"><span class="gsxg-tag">What We Look For</span><h3>Staff Expectations</h3><ul class="gsxg-list">' +
        '<li>Professional communication and patience.</li><li>Reliable activity and willingness to learn.</li>' +
        '<li>No abuse of permissions or favoritism.</li><li>Ability to follow leadership direction and document issues clearly.</li></ul></div>' +
        '</div></section>' +

        # Application CTA
        '<section class="gsxg-section"><div class="gsxg-inner gsxg-footer-cta">' +
        '<div class="gsxg-kicker">Application Path</div><h2>Ready to Help?</h2>' +
        '<p class="gsxg-muted" style="margin:0 auto 24px">Complete the staff application and agreement form. Include the server you play on, your experience, availability, and why you want to help GSXG grow.</p>' +
        '<div class="gsxg-actions" style="justify-content:center">' +
        '<a class="gsxg-btn primary" href="https://form.jotform.com/261488914584168" target="_blank" rel="noopener">Sign Staff Application</a>' +
        '<a class="gsxg-btn dark" href="https://ops.goldstandardxgaming.org/view/gsx-community-bot" target="_blank" rel="noopener">Open Support Ticket</a></div></div></section>' +

        # Migrated old staff site content
        '<section class="gsxg-section gsxg-band"><div class="gsxg-inner">' +
        '<div class="gsxg-heading"><div><div class="gsxg-kicker">Original Staff Site Content</div><h2>Staff Program Details</h2></div>' +
        '<p>Brought over from the old staff.goldstandardxgaming.org site.</p></div>' +
        '<div class="gsxg-grid">' +
        '<div class="gsxg-readable"><h3>Why Become Staff?</h3><ul><li>Be part of a growing multi-game survival community.</li>' +
        '<li>Help create an enjoyable environment for players across multiple games.</li>' +
        '<li>Work alongside experienced administrators and leadership.</li>' +
        '<li>Earn advancement through Trial Moderator, Moderator, Admin, Head Admin, and Leadership.</li></ul></div>' +
        '<div class="gsxg-readable"><h3>Professional Tools</h3><ul><li>Unified Operations Panel</li><li>ServerOpsX</li>' +
        '<li>Ticket management systems</li><li>Uptime monitoring</li><li>BattleMetrics integration</li><li>Server Armour</li>' +
        '<li>Knowledge base and documentation</li></ul></div>' +
        '<div class="gsxg-readable"><h3>What We Look For</h3><ul><li>Mature and professional</li><li>Active and reliable</li>' +
        '<li>Team-oriented</li><li>Willing to learn</li><li>Strong communicator</li><li>Fair and unbiased</li>' +
        '<li>Passionate about gaming</li></ul></div></div>' +
        '<div class="gsxg-grid">' +
        '<div class="gsxg-readable"><h3>Staff Expectations</h3><ul><li>Assist players professionally.</li>' +
        '<li>Handle tickets and support requests.</li><li>Enforce community rules fairly.</li>' +
        '<li>Document actions when required.</li><li>Maintain confidentiality when appropriate.</li>' +
        '<li>Represent GSXG positively.</li></ul></div>' +
        '<div class="gsxg-readable"><h3>Administrative Experience</h3><ul><li>Community management</li><li>Conflict resolution</li>' +
        '<li>Team leadership</li><li>Server administration</li><li>Discord moderation</li><li>Ticket support</li>' +
        '<li>Event planning</li></ul></div>' +
        '<div class="gsxg-readable"><h3>Original Staff Roster</h3><p><strong>Owner:</strong> Tobbie330<br>' +
        '<strong>Discord Developers:</strong> Carl<br><strong>Creator:</strong> Personal Discord, Company Discord<br>' +
        '<strong>Head Admin:</strong> Chuck, TK, Chad<br><strong>Plugin Assistance:</strong> Avva<br>' +
        '<strong>Admins:</strong> Jason, Adam<br><strong>Moderators:</strong> To be expanded as staff grows.</p></div>' +
        '</div></div></section>' +
        '</div>'
    )
    return page_item("Join Staff", "staff", STAFF_ID, content, parent=0, menu_order=0)


def page_item(title, slug, pid, content, parent=0, menu_order=0):
    return """\t<item>
\t\t<title><![CDATA[{title}]]></title>
\t\t<link>{site}/{slug}/</link>
\t\t<pubDate>Fri, 26 Jun 2026 12:00:00 +0000</pubDate>
\t\t<dc:creator><![CDATA[Codex]]></dc:creator>
\t\t<guid isPermaLink="false">{site}/?page_id={pid}</guid>
\t\t<description></description>
\t\t<content:encoded><![CDATA[{content}]]></content:encoded>
\t\t<excerpt:encoded><![CDATA[]]></excerpt:encoded>
\t\t<wp:post_id>{pid}</wp:post_id>
\t\t<wp:post_date><![CDATA[{date}]]></wp:post_date>
\t\t<wp:post_date_gmt><![CDATA[{date}]]></wp:post_date_gmt>
\t\t<wp:post_modified><![CDATA[{date}]]></wp:post_modified>
\t\t<wp:post_modified_gmt><![CDATA[{date}]]></wp:post_modified_gmt>
\t\t<wp:comment_status><![CDATA[closed]]></wp:comment_status>
\t\t<wp:ping_status><![CDATA[closed]]></wp:ping_status>
\t\t<wp:post_name><![CDATA[{slug}]]></wp:post_name>
\t\t<wp:status><![CDATA[publish]]></wp:status>
\t\t<wp:post_parent>{parent}</wp:post_parent>
\t\t<wp:menu_order>{menu_order}</wp:menu_order>
\t\t<wp:post_type><![CDATA[page]]></wp:post_type>
\t\t<wp:post_password><![CDATA[]]></wp:post_password>
\t\t<wp:is_sticky>0</wp:is_sticky>
\t\t<wp:postmeta>
\t\t\t<wp:meta_key><![CDATA[_wp_page_template]]></wp:meta_key>
\t\t\t<wp:meta_value><![CDATA[default]]></wp:meta_value>
\t\t</wp:postmeta>
\t</item>
""".format(title=title, slug=slug, pid=pid, content=content, parent=parent,
           menu_order=menu_order, site=SITE, date=DATE)


HEADER = """<?xml version="1.0" encoding="UTF-8" ?>
<!-- GoldStandardX Gaming - per-server pages + updated Our Servers + Join Staff -->
<!-- Import with Tools > Import > WordPress. See README before importing. -->
<rss version="2.0"
\txmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
\txmlns:content="http://purl.org/rss/1.0/modules/content/"
\txmlns:wfw="http://wellformedweb.org/CommentAPI/"
\txmlns:dc="http://purl.org/dc/elements/1.1/"
\txmlns:wp="http://wordpress.org/export/1.2/"
>
<channel>
\t<title>GoldStandardX Gaming</title>
\t<link>{site}</link>
\t<description>Professional Multi-Game Community</description>
\t<pubDate>Fri, 26 Jun 2026 12:00:00 +0000</pubDate>
\t<language>en-US</language>
\t<wp:wxr_version>1.2</wp:wxr_version>
\t<wp:base_site_url>{site}</wp:base_site_url>
\t<wp:base_blog_url>{site}</wp:base_blog_url>
\t<wp:author><wp:author_id>2</wp:author_id><wp:author_login><![CDATA[Codex]]></wp:author_login><wp:author_email><![CDATA[tobbie.dinning@tobbieJohn.com]]></wp:author_email><wp:author_display_name><![CDATA[Tobbie Business]]></wp:author_display_name><wp:author_first_name><![CDATA[Tobbie]]></wp:author_first_name><wp:author_last_name><![CDATA[Business]]></wp:author_last_name></wp:author>
\t<generator>https://wordpress.org/?v=7.0</generator>
""".format(site=SITE)

FOOTER = "</channel>\n</rss>\n"


def write_file(name, items):
    out = HEADER + "".join(items) + FOOTER
    with open(name, "w", encoding="utf-8") as f:
        f.write(out)
    print("Wrote %s (%d bytes)" % (name, len(out)))


def main():
    # File 1: 15 NEW per-server pages — safe to import, nothing to delete first.
    hubs = []
    pid = FIRST_HUB_ID
    for s in SERVERS:
        hubs.append(hub_page(s, pid))
        pid += 1
    write_file("01-server-pages.xml", hubs)

    # File 2: replacements for the two existing pages (trash the old ones first).
    write_file("02-replace-our-servers-and-staff.xml", [our_servers_page(), staff_page()])

    # Combined file (everything in one) for users who prefer a single import.
    write_file("goldstandardx-gaming-update.xml",
               [our_servers_page(), staff_page()] + hubs)
    print("Server hubs: %d" % len(SERVERS))


if __name__ == "__main__":
    main()

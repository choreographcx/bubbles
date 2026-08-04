Bubbles Pet Rescue WordPress Theme
Version 1.2.4

Recommended Installation Order
1. Upload and activate bubbles-pet-rescue-core.zip under Plugins > Add New.
2. Upload and activate the Bubbles Pet Rescue theme under Appearance > Themes.
3. Go to Bubbles Rescue in WordPress Admin to add and manage dogs and cats.
4. Go to Appearance > Customize > Bubbles Rescue Settings to review the Amazon Wishlist URL, contact links, and home-page copy.
5. Add menu links for Dogs, Cats, Adoption Application, Foster Application, and Wishlist Needs.

Why There Is a Separate Plugin
The Bubbles Pet Rescue Core plugin owns dogs, cats, statuses, structured pet fields, galleries, and saved applications. The theme owns the visual design. This keeps rescue records safe when the theme is updated or replaced.

Dog Directory Design
The dogs archive now uses a compact, centered directory inspired by the supplied Chunkz & Tubz reference:
- Dogs are grouped into Adoptable Dogs and Available Soon.
- Adopted dogs are excluded from the public adoption directory.
- Available Soon includes Coming Soon, Medical Care, and Adoption Pending statuses.
- Cards use a consistent image ratio and show the dog’s name, age, gender, and breed.
- The layout is three columns on desktop, two on tablet, and one on small screens.

Pet Profile Design
Individual dog and cat profiles now use an editorial two-column layout:
- Breadcrumb navigation
- “Meet [Pet Name]” heading and the pet story on the left
- Adoption, foster, and Amazon wishlist links
- Image carousel with arrows, indicators, touch support, and a full-screen viewer
- A fixed square carousel frame that crops portrait and landscape photos consistently
- Age, gender, and breed summary beneath the carousel
- Structured care, compatibility, training, health, and home-suitability facts
- Personality tags and longer health or compatibility notes

Pet Fields
Each dog and cat can include:
- Featured image plus a reorderable image gallery
- Age, age range, gender, size, weight, color, coat, and location
- Multiple breeds and a custom breed or mix
- Compatibility with dogs, cats, and children
- House, crate, and leash training
- Apartment suitability, time alone, and energy level
- Spay/neuter, vaccination, microchip, deworming, and special-needs status
- Personality tags
- Health and compatibility notes
- Separate adoption and foster application links

Existing Data
The plugin and theme retain the existing dog, cat, application, pet_status, and _bpr_* identifiers. Existing pets and saved applications remain available. Legacy fields continue to display.

Shortcodes
[bubbles_adoption_application]
[bubbles_foster_application]
[bpr_application_form type="adoption"]
[bpr_application_form type="foster"]

Forms
The Adoption Application and Foster Application page templates display editor content and shortcodes. The built-in form appears only when the editor is empty.

Technical Notes
The theme uses Bootstrap 5.3.3 and Bootstrap Icons through the jsDelivr CDN. It uses Nunito Sans and Caveat through Google Fonts.

Version 1.2.4
- Replaces the oversized homepage pet grid with one responsive featured-pet layout.
- Adds square image handling for the homepage feature and all standard pet cards.
- Adds Customizer controls for the homepage rescue copy and featured pet selection.

Version 1.2.4 adds mobile overflow protection and hard square media frames across the homepage, dog archive, and individual pet profiles.

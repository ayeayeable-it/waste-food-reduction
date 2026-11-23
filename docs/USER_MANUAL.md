# RescueBite — User Manual

## 1. Buyer Guide
1. **Register or Sign In:** Visit `/register` and select the **Buyer** role, or log in at `/login`.
2. **Discover Nearby Food:** Open **Discover** (`/buyer/discover`) to search by name, filter by category/price/distance, or click **Use My Location** to sort by nearest store.
3. **Interactive Map:** Open **Map** (`/buyer/map`) to view nearby store pins, click any marker for live surplus listings, or click anywhere on the map to set a manual location.
4. **Reserve Food:** Open a listing's detail page, choose a quantity, review all food details (expiry label, allergens, storage, collection instructions), check all 6 acknowledgement checkboxes, and click **Confirm Reservation**.
5. **Collect In-Store:** Present your unique reservation reference (e.g., `RB-20261123-XXXXXX`) at the store counter before the expiry time (up to 3 hours or the collection deadline, whichever is earlier).
6. **Cancel & Track Savings:** View active/past orders and total consumer savings under **Reservations** (`/buyer/reservations`). Pending reservations can be cancelled before expiry.

## 2. Seller Guide
1. **Register Stores:** Open **Stores → Register New Store** (`/seller/stores/create`), enter store details, and click/drag the map pin to set exact GPS coordinates.
2. **Publish Surplus Food:** Open **Listings → Create Food Listing**, upload an optional photo, set original & discounted prices (`discounted < original`), enter available quantity, expiry label, storage instructions, allergens, and collection deadline.
3. **Manage Listings:** Manually adjust available quantities or click **Close** to stop new reservations.
4. **Confirm Collection:** Open **Reservations** (`/seller/reservations`), enter or search the buyer's reservation reference, and click **Confirm Collection**. Partial collection is not supported.
5. **Reports:** View store-level collection rates, revenue, and consumer savings under **Reports** (`/seller/reports`).

## 3. Administrator Guide
1. **Dashboard:** Monitor platform-wide totals, reservation outcome charts, and recent audit activity at `/admin/dashboard`.
2. **User & Store Governance:** Activate or deactivate user accounts (`/admin/users`) and stores (`/admin/stores`).
3. **Categories & Listings:** Manage food categories (`/admin/categories`) and force-close non-compliant listings (`/admin/listings`).
4. **Broadcast Notifications:** Send announcements or collection reminders to buyers/sellers at `/admin/notifications`.
5. **Audit Logs & System Reports:** Inspect immutable audit logs (`/admin/audit-logs`) and export date-filtered CSV reports (`/admin/reports`).

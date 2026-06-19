## Goal
Build complete multi-boutique auth, subscription, RBAC & module management system for the Hanooti SaaS platform.

## Constraints & Preferences
- Follow existing codebase patterns (Doctrine entities, API Platform resources, State providers/processors, REST controllers)
- Reuse existing entities/services when possible, avoid duplication
- Multi-boutique SaaS architecture: all data isolated by boutique
- Roles: SUPER_ADMIN, BOUTIQUE_ADMIN, EMPLOYEE (ROLE_CAISSIER), CUSTOMER
- User statuses: PENDING, ACTIVE, SUSPENDED, REJECTED
- PostgreSQL 16 via Docker, all commands inside `docker compose run --rm app php bin/console ...`
- Triple-check module access: PlatformModule.is_enabled → SubscriptionModule.is_allowed → ShopModule.is_enabled
- Permissions checked via RolePermission (role_code → permission code) at backend level only

## Progress
### Done
- **SubscriptionPlan entity**: name, description, durationMonths, priceTnd, isFree, isVisible, isActive, modules (JSON) + OneToMany subscriptionModules
- **SubscriptionPlanModule entity**: 20 modules (reviews→pos) with code, name, description, category, icon, isCore
- **SubscriptionRequest entity**: boutique→Boutique, subscriptionPlan→SubscriptionPlan, status (Pending/Approved/Rejected), requestedAt, approvedAt, approvedBy
- **Subscription entity**: nullable subscriptionPlan ManyToOne
- **API**: SubscriptionPlanResource (CRUD SUPER_ADMIN `/api/admin/subscription-plans`)
- **API**: SubscriptionRequestResource (POST BOUTIQUE_ADMIN `/api/boutiques/{id}/subscription-requests`, approve/reject SUPER_ADMIN)
- **API**: BOUTIQUE_ADMIN GET `/api/boutique/subscription-plans` (active+visible only)
- **Renewal logic**: preserves remaining days on active subscription, fresh start if expired
- **Plans seeded**: Starter (free), Business 3/6/12mois, Premium 12mois — each with SubscriptionModule records
- **Permission entity**: code, name, module, description — master permission list, 65 permissions seeded
- **PlatformModule entity**: module→SubscriptionPlanModule, isEnabled, reasonDisabled — global SUPER_ADMIN toggle
- **ShopModule entity**: boutique→Boutique, module→SubscriptionPlanModule, isEnabled — per-boutique config
- **ModuleAccessService**: triple-check (Platform → Plan → Shop) with `isModuleEnabled()` + `getAccessibleModules()`; uses SubscriptionModule entity + Redis cache (600s TTL); fallback to SubscriptionPlan.modules JSON
- **ModuleCacheService**: Redis-backed cache for platform/plan/shop module maps; `deleteAllPlatform/deletePlan/deleteShop` invalidation
- **SubscriptionModule entity**: plan_id→SubscriptionPlan, module_id→SubscriptionPlanModule, is_allowed — normalized join entity replacing JSON column
- **SubscriptionModuleRepository**: findByPlan, findOneByPlanAndModule, findAllowedByPlan, findAllowedModuleCodes
- **API**: SubscriptionModuleResource (CRUD SUPER_ADMIN `/api/admin/subscription-modules` + `/api/admin/subscription-plans/{planId}/modules`)
- **API**: PermissionResource (CRUD SUPER_ADMIN `/api/admin/permissions`)
- **API**: PlatformModuleResource (CRUD SUPER_ADMIN `/api/admin/platform-modules`)
- **API**: ShopModuleResource (CRUD BOUTIQUE_ADMIN `/api/boutiques/{id}/modules`)
- **DTOs+Providers+Processors**: Permission, PlatformModule, ShopModule, SubscriptionModule — following existing patterns
- **Permissions seeded**: 65 permissions across catalogue, commandes, clients, cms, employés, marketing, facturation, abonnements, boutique, rapports
- **Migration** `Version20260629084955`: added `icon`/`is_core` to subscription_plan_module, created permission/platform_module/shop_module tables
- **Migration** `Version20260629085037`: removed `is_core` default
- **Migration** `Version20260629090001`: created subscription_module table with unique (plan_id, module_id)
- **Schema in sync**, CS fixer clean, tests pass (1 test, 3 assertions)

### Done (this session)
- **BoutiqueSettings entity** — added 22 new fields (theme, coverImage, description, fontFamily, fontSize, borderRadius, headerConfig, footerConfig, deliveryApiEndpoint, analytics, loyalty, orderMode, maintenance, socialLinks, navigationItems, guestCheckoutFields, slogan, favicon, enableEmailVerification, enableCustomerEmailVerification, createAccountAfterOrder, enableLoyalty) with full getters/setters
- **BoutiqueSettingsInput/Output DTOs** — all ~40 fields including JSON columns
- **BoutiqueSettingsResource updated** — uses SettingsProvider/SettingsProcessor instead of passthrough; maps 40+ fields
- **SettingsProvider** — full BoutiqueSettings → BoutiqueSettingsOutput mapping (40+ fields)
- **SettingsProcessor** — PATCH with `applyScalarFields` + `applyJsonFields`, `updateContact` fix, cache invalidation
- **Theme entity** — name, code (unique), previewImage, isActive, isDefault, timestamps
- **ThemeRepository** — findActive, findDefault, findOneByCode, clearDefault
- **ThemeInput/Output DTOs** — name, code, previewImage, isActive, isDefault
- **ThemeResource** — SUPER_ADMIN CRUD `/api/admin/themes` + PUBLIC GET `/api/boutiques/{id}/themes`
- **ThemeProvider/Processor** — SUPER_ADMIN CRUD with default theme clearing
- **Menu entity** — boutique FK, name, position (HEADER/FOOTER/OTHER), isActive, OneToMany items, timestamps
- **MenuItem entity** — menu FK, title, type (HOME/PAGE/CATEGORY/PRODUCT/URL/CONTACT), target, parent self-ref FK, position, isActive, timestamps
- **MenuRepository/MenuItemRepository** — boutique-scoped queries
- **MenuInput/Output, MenuItemInput/Output DTOs** — all specs with parent hierarchy
- **MenuResource** — boutique-scoped CRUD + nested menu item CRUD
- **MenuProvider/Processor** — boutique-scoped with nested items serialization, parent hierarchy
- **Announcement entity rewritten** — boutique FK (nullable for global), content, displayType, title, colors, icon, linkUrl, priority, isDismissible, displayMode (FIXED/SCROLLING/SLIDER), position (TOP_PAGE/ABOVE_HEADER/BELOW_HEADER/ABOVE_FOOTER/HOME_TOP/HOME_BOTTOM), displayPages JSON, active, isGlobal, startsAt, endsAt, isVisible()
- **AnnouncementInput/Output DTOs** — all specs (split to individual files for PSR-4 autoloading)
- **AnnouncementResource** — boutique-scoped CRUD + global SUPER_ADMIN endpoints
- **AnnouncementProvider/Processor** — boutique + global support, cache invalidation
- **FrontOfficeCacheService** — Redis cache for `shop:{id}:settings` and `shop:{id}:menus`, serializeSettings() method, invalidateAll()/invalidateSettings()/invalidateMenus()/invalidateSeo()
- **Migration** `Version20260629093104` — created theme, menu, menu_item tables; added announcement columns (title, border_color, icon, link_url, priority, is_dismissible, display_mode, position, display_pages, is_global); added boutique_settings columns (cover_image, description, font_family, font_size, border_radius, header_config, footer_config)
- **Security.yaml** — added PUBLIC_ACCESS routes for settings, menus, themes; SUPER_ADMIN routes for admin/themes and admin/announcements
- **PHP-CS-Fixer clean**, schema validated, tests pass (1 test, 3 assertions)

### In Progress
- *(none)*

### Blocked
- *(none)*

## Key Decisions
- `SubscriptionPlanModule` reused as master module definition (no separate Module entity)
- Plan-level module gating uses existing `SubscriptionPlan.modules` JSON column as fallback; primary source is SubscriptionModule join entity
- Triple-check via ModuleAccessService: PlatformModule (global) → SubscriptionPlan.modules (plan) → ShopModule (boutique)
- RolePermission uses `role_code` + `permission` string directly — no join table for roles
- PlanType enum kept for backward compatibility with initial Free subscription; all new flows use SubscriptionPlan
- Permissions created via dedicated entity (not embedded in modules) for flexible role→permission mapping

## Next Steps
1. Add permission-check middleware/gate for API Platform operations
2. Update BoutiqueContext to support permission-based access (role + permission per boutique)
3. Create SUPER_ADMIN UI for module/plan management
4. Create BOUTIQUE_ADMIN UI for shop module config + employee permissions
5. Add cache layer for user permissions (6h TTL, invalidate on changes)

## Critical Context
- All commands inside Docker: `docker compose run --rm app php bin/console ...`
- RBAC tables (user_shop, role_permission) from migration `Version20260627100000`
- Subscription tables from migration `Version20260629084023` + `Version20260629084955`
- 20 modules: reviews, wishlist, loyalty, coupons, promotions, blog, brands, multi_address, chatbot, seo_advanced, custom_domain, analytics, delivery_tracking, wholesale, gift_cards, newsletter, abandoned_cart, order_printing, social_login, pos
- 5 plans: Starter (0 DT), Business 3mois (99 DT), 6mois (179 DT), 12mois (299 DT), Premium 12mois (499 DT)
- 65 permissions seeded across 10 modules (catalogue, commandes, clients, cms, employés, marketing, facturation, abonnements, boutique, rapports)
- ModuleAccessService injected with PlatformModuleRepository + ShopModuleRepository + EntityManagerInterface

## Relevant Files
- `src/Entity/SubscriptionPlan.php` — plan definition with modules JSON
- `src/Entity/SubscriptionPlanModule.php` — module definition with icon + isCore
- `src/Entity/SubscriptionRequest.php` — request/approve/reject flow
- `src/Entity/Subscription.php` — added subscriptionPlan FK
- `src/Entity/Permission.php` — master permission list (65 seeded)
- `src/Entity/PlatformModule.php` — global SUPER_ADMIN toggle
- `src/Entity/ShopModule.php` — per-boutique module config
- `src/Entity/RolePermission.php` — role→permission mapping
- `src/Service/Module/ModuleAccessService.php` — triple-check module access
- `src/Command/SeedSubscriptionModulesCommand.php` — 20 modules with icons
- `src/Command/SeedSubscriptionPlansCommand.php` — 5 plans
- `src/Command/SeedPermissionsCommand.php` — 65 permissions
- `src/ApiResource/SubscriptionPlan/SubscriptionPlanResource.php`
- `src/ApiResource/SubscriptionRequest/SubscriptionRequestResource.php`
- `src/ApiResource/Permission/PermissionResource.php`
- `src/ApiResource/PlatformModule/PlatformModuleResource.php`
- `src/ApiResource/ShopModule/ShopModuleResource.php`
- `migrations/Version20260629084955.php` — icon/is_core + 3 new tables

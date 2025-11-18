# Hotspot ISP Billing System - Development Guide

## 🎨 Design Consistency Standards
- **Primary color**: #E6801E (orange theme across all components)
- **UI Framework**: Tailwind CSS 
- **Design reference**: Clean design like registration and login blades
- **Typography**: Instrument Sans font family
- **Loading states**: Professional spinners and overlays (see SPINNER_FEATURES.md)
- **Forms**: Progressive disclosure with wizards and validation
- **Forms**: consider modals for some CRUD forms that have a few


## 🏗️ Architecture Patterns

### Technology Stack
- **Backend**: Laravel 12+ with PHP 8.2+
- **Frontend**: Livewire 3.6+ for reactive components
- **Database**: mysql for development, supports multi-tenant architecture
- **Styling**: Tailwind CSS 4.0+ with Vite build system
- **Icons**: Blade Heroicons 2.6+
- **Authentication**: Laravel Sanctum

### Core Components Structure
- **Models**: Multi-tenant aware (Tenant, Customer, Product, Service, Voucher, RadiusIdentity)
- **Livewire Components**: Follow naming pattern `{Entity}Component.php` (e.g., `VoucherManagerComponent.php`)
- **Views**: Located in `resources/views/livewire/` with kebab-case naming
- **Layouts**: Use `component.layouts.app` for authenticated pages, component-based layouts

### Database Patterns
- **Tenant-scoped**: All business models include `tenant_id` foreign key
- **Service types**: 'hotspot' and 'broadband' (ISP services)
- **Status patterns**: Use enums for status fields (active/suspended/terminated)
- **JSON columns**: Use for flexible settings (branding, tax_profile, settings)

## 🔧 Development Patterns

### Livewire Component Standards
```php
// Standard component structure
class ComponentName extends Component
{
    use WithPagination; // When needed
    
    public $selectedTenant; // Always include for tenant-scoped components
    public $showModal = false; // For modal states
    
    protected $rules = []; // Define validation rules
    
    public function mount() {
        $this->selectedTenant = Tenant::first()?->id;
    }
    
    public function render() {
        return view('livewire.component-name')
               ->layout('layouts.app'); // Use layouts consistently
    }
}
```

### Form Patterns
- **Validation**: Always validate on both frontend and backend
- **Reset methods**: Include `resetForm()` methods for modals
- **Flash messages**: Use `session()->flash('message', $text)` for feedback
- **Loading states**: Implement spinners for all async operations

### Route Organization
- **Public routes**: No middleware (/, /register, /login)
- **Protected routes**: Use auth middleware
- **Resource routes**: Follow RESTful patterns
- **Tenant-scoped**: Most routes should filter by tenant

## 🗃️ Data Patterns

### Multi-Tenant Architecture
- **Tenant model**: Central entity with slug, settings, branding
- **Scoped queries**: Always filter by `tenant_id`
- **Relationships**: Most models belong to tenant
- **Data isolation**: Prevent cross-tenant data access

### Service Types Implementation
- **Hotspot services**: Use voucher-based authentication
- **ISP services**: Use subscriber-based authentication  
- **Products**: Linked to service types with pricing
- **RADIUS integration**: RadiusIdentity model for auth

### Common Model Patterns
```php
// Standard model relationships
public function tenant(): BelongsTo {
    return $this->belongsTo(Tenant::class);
}

// Scoped queries
public function scopeForTenant($query, $tenantId) {
    return $query->where('tenant_id', $tenantId);
}

// JSON casting
protected $casts = [
    'settings' => 'array',
    'branding' => 'array',
];
```

## 🎯 Common Tasks & Solutions

### Authentication Flow
1. **Public registration**: TenantRegistrationComponent (Phase 1)
2. **Profile setup**: ProfileSetupComponent (Phase 2 - 4 steps)
3. **Login redirect**: Check profile completion status
4. **Dashboard access**: Requires completed profile

### RADIUS Integration
- **Sync service**: RadiusSyncService for managing RADIUS users
- **Identity management**: RadiusIdentityObserver for auto-sync
- **Network devices**: NetworkDevice model for NAS management

### Voucher Management
- **Generation**: Batch creation with unique codes
- **States**: unused/used/expired/disabled
- **Export**: PDF/CSV export functionality
- **Service binding**: Link to hotspot services only

### File Structure Patterns
- **Controllers**: Minimal, delegate to Livewire components
- **Services**: Business logic in dedicated service classes
- **Observers**: Auto-sync and side effects
- **Migrations**: Follow timestamp naming, cascade deletes

## 🚀 Performance & Best Practices

### Database Optimization
- **Eager loading**: Use `with()` for relationships
- **Pagination**: Always paginate large datasets (20 items default)
- **Indexing**: Add indexes on foreign keys and search fields

### Frontend Optimization
- **Lazy loading**: Use `wire:loading` for better UX
- **Debouncing**: Implement on search inputs
- **Caching**: Cache frequently accessed tenant data

### Security Patterns
- **Input validation**: Server-side validation always required
- **CSRF protection**: Enabled by default, maintain tokens
- **SQL injection**: Use Eloquent ORM, avoid raw queries
- **XSS protection**: Blade templating auto-escapes

## 🔍 Debugging & Development

### Common Issues & Solutions
- **Tenant context**: Always verify `$selectedTenant` is set
- **Livewire reactivity**: Use `$this->dispatch('refresh')` for updates
- **Cache issues**: Run `php artisan optimize:clear` after changes
- **Migration conflicts**: Use specific migration paths for new tables

### Development Commands
```bash
# Setup & maintenance
php artisan optimize:clear
php artisan migrate
php artisan db:seed

# Radius operations  
php artisan radius:sync

# Testing
php artisan serve
php artisan tinker
```

## 💡 Problem-Solving Approach
1. **Identify**: Understand the user requirement and business context
2. **Analyze**: Check existing patterns in codebase
3. **Implement**: Follow established conventions and patterns
4. **Test**: Verify functionality across tenant contexts
5. **Iterate**: Refine based on feedback and edge cases

## 📋 Quick Reference Checklist
- [ ] Component includes tenant scoping
- [ ] Validation rules defined and implemented
- [ ] Loading states with spinners implemented
- [ ] Flash messages for user feedback
- [ ] Consistent styling with Tailwind classes
- [ ] Relationships properly defined
- [ ] Migration includes proper constraints
- [ ] Tests cover main functionality paths

This guide ensures consistency, improves development speed, and maintains code quality across the billing system.
# Filament to Blade/Controllers/DataTables Migration Walkthrough

## Overview
This document tracks the migration from Filament Admin Panel to a custom Blade-based admin interface using Controllers and DataTables (Yajra DataTables for Laravel).

## Resources to Migrate

### 1. Core Resources (13 total)
- [ ] **Medicines** (`MedicineResource`)
- [ ] **Users** (`UserResource`)
- [ ] **Pharmacies** (`PharmacyResource`)
- [ ] **Laboratories** (`LaboratoryResource`) - *Partially migrated (create/edit already in Blade)*
- [ ] **Orders** (`OrderResource`)
- [ ] **Offers** (`OfferResource`)
- [ ] **MedicalTests** (`MedicalTestResource`)
- [ ] **MedicalTestOffers** (`MedicalTestOfferResource`)
- [ ] **ClientRequests** (`ClientRequestResource`)
- [ ] **Clients** (`ClientResource`)
- [ ] **Areas** (`AreaResource`)
- [ ] **Cities** (`CityResource`)
- [ ] **Governorates** (`GovernorateResource`)

## Migration Structure

### Directory Structure
```
app/
├── Http/
│   └── Controllers/
│       └── Admin/
│           ├── DashboardController.php
│           ├── MedicineController.php
│           ├── UserController.php
│           ├── PharmacyController.php
│           ├── LaboratoryController.php (exists)
│           ├── OrderController.php
│           ├── OfferController.php (exists)
│           ├── MedicalTestController.php
│           ├── MedicalTestOfferController.php
│           ├── ClientRequestController.php
│           ├── ClientController.php
│           ├── AreaController.php
│           ├── CityController.php
│           └── GovernorateController.php

resources/
└── views/
    └── admin/
        ├── layouts/
        │   └── admin.blade.php (main admin layout)
        ├── dashboard.blade.php
        ├── medicines/
        │   ├── index.blade.php
        │   ├── create.blade.php
        │   ├── edit.blade.php
        │   └── show.blade.php (if needed)
        ├── users/
        ├── pharmacies/
        ├── laboratories/ (exists)
        ├── orders/
        ├── offers/
        ├── medical-tests/
        ├── medical-test-offers/
        ├── client-requests/
        ├── clients/
        ├── areas/
        ├── cities/
        └── governorates/
```

## Step-by-Step Migration Process

### Phase 1: Setup & Infrastructure

#### Step 1.1: Install Yajra DataTables
```bash
composer require yajra/laravel-datatables-oracle
php artisan vendor:publish --tag=datatables
```

#### Step 1.2: Create Admin Layout
- **File**: `resources/views/admin/layouts/admin.blade.php`
- **Features**:
  - Sidebar navigation with all resource links
  - Top header with user menu and logout
  - Main content area
  - Include DataTables CSS/JS
  - Include Bootstrap or Tailwind CSS (match existing design)

#### Step 1.3: Create Dashboard Controller & View
- **Controller**: `app/Http/Controllers/Admin/DashboardController.php`
- **View**: `resources/views/admin/dashboard.blade.php`
- **Features**:
  - Statistics cards (counts for each resource)
  - Recent activity
  - Quick links

#### Step 1.4: Update Routes
- **File**: `routes/web.php`
- Add admin route group with auth middleware
- Add routes for each resource (index, create, store, edit, update, destroy)
- Add DataTables AJAX routes

### Phase 2: Resource Migration (Repeat for each resource)

#### For each resource (e.g., Medicines):

**Step 2.1: Create Controller**
- **File**: `app/Http/Controllers/Admin/MedicineController.php`
- **Methods**:
  - `index()` - Display DataTable
  - `create()` - Show create form
  - `store()` - Store new record
  - `edit($id)` - Show edit form
  - `update($id)` - Update record
  - `destroy($id)` - Delete record
  - `data()` - AJAX endpoint for DataTables

**Step 2.2: Create Index View with DataTable**
- **File**: `resources/views/admin/medicines/index.blade.php`
- **Features**:
  - DataTable initialization
  - Search/filter inputs
  - Create button
  - Edit/Delete action buttons
  - Bulk actions (if needed)

**Step 2.3: Create Form Views**
- **File**: `resources/views/admin/medicines/create.blade.php`
- **File**: `resources/views/admin/medicines/edit.blade.php`
- **Features**:
  - Convert Filament form schema to HTML form
  - Validation error display
  - CSRF token
  - Form submission handling

**Step 2.4: Create DataTables AJAX Method**
- In controller, implement `data()` method using Yajra DataTables
- Map Filament table columns to DataTables columns
- Implement search, sorting, filtering
- Handle relationships (eager loading)

**Step 2.5: Add Routes**
- Add resource routes to `routes/web.php`
- Add DataTables AJAX route

### Phase 3: Special Cases

#### 3.1: Resources with Relations (ClientRequests, Orders, Offers)
- Handle nested resources (e.g., RequestLines, OrderLines)
- Create separate controllers for relation managers if needed
- Or handle inline in main resource views

#### 3.2: Resources with File Uploads (ClientRequests - images)
- Implement file upload handling
- Image preview functionality
- File storage management

#### 3.3: Resources with Maps (Pharmacies, Laboratories)
- Integrate map picker (Leaflet/Google Maps)
- Handle lat/lng coordinates
- Display on map in forms

### Phase 4: Cleanup

#### Step 4.1: Disable Filament Panel
- **File**: `app/Providers/Filament/AdminPanelProvider.php`
- Comment out or remove panel registration
- Or set `->login(false)` and remove resource discovery

#### Step 4.2: Remove Filament Dependencies (Optional)
- Remove Filament package if not needed elsewhere
- Clean up Filament-specific code in models (e.g., `FilamentUser` interface)

#### Step 4.3: Update Authentication
- Ensure custom login works with new admin routes
- Update middleware as needed
- Test authentication flow

## DataTables Implementation Details

### Column Mapping Example (Medicines)
```php
// From Filament MedicinesTable.php
TextColumn::make('name')->searchable()->sortable()
TextColumn::make('arabic')->searchable()
TextColumn::make('price')->money('EGP')->sortable()

// To DataTables
return DataTables::of(Medicine::query())
    ->addColumn('name', function ($medicine) {
        return $medicine->name;
    })
    ->addColumn('arabic', function ($medicine) {
        return $medicine->arabic;
    })
    ->addColumn('price', function ($medicine) {
        return 'EGP ' . number_format($medicine->price, 2);
    })
    ->addColumn('actions', function ($medicine) {
        return view('admin.medicines.actions', compact('medicine'))->render();
    })
    ->rawColumns(['actions'])
    ->make(true);
```

### Frontend DataTable Initialization
```javascript
$(document).ready(function() {
    $('#medicines-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.medicines.data') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'arabic', name: 'arabic' },
            { data: 'price', name: 'price' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });
});
```

## Form Conversion Guide

### Filament Form Component → HTML Input

| Filament Component | HTML Equivalent |
|-------------------|-----------------|
| `TextInput::make('name')` | `<input type="text" name="name">` |
| `Textarea::make('description')` | `<textarea name="description"></textarea>` |
| `Toggle::make('is_active')` | `<input type="checkbox" name="is_active">` |
| `Select::make('status')` | `<select name="status">...</select>` |
| `DatePicker::make('date')` | `<input type="date" name="date">` |
| `FileUpload::make('image')` | `<input type="file" name="image">` |

### Section → HTML Section
```blade
<!-- Filament Section -->
Section::make('Basic Information')

<!-- HTML Equivalent -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Basic Information</h5>
    </div>
    <div class="card-body">
        <!-- Form fields -->
    </div>
</div>
```

## Testing Checklist

For each migrated resource:
- [ ] List page loads with DataTable
- [ ] DataTable displays data correctly
- [ ] Search functionality works
- [ ] Sorting works
- [ ] Filters work (if applicable)
- [ ] Create form displays correctly
- [ ] Create form validation works
- [ ] Create saves data correctly
- [ ] Edit form loads with existing data
- [ ] Edit form validation works
- [ ] Edit updates data correctly
- [ ] Delete functionality works
- [ ] Bulk actions work (if applicable)
- [ ] Relationships display correctly
- [ ] File uploads work (if applicable)
- [ ] Maps work (if applicable)

## Migration Order (Recommended)

1. **Simple Resources First** (no complex relations):
   - Areas
   - Cities
   - Governorates
   - MedicalTests

2. **Medium Complexity**:
   - Medicines
   - Users
   - Pharmacies
   - Laboratories (already partially done)

3. **Complex Resources** (with relations):
   - ClientRequests
   - Orders
   - Offers
   - MedicalTestOffers
   - Clients

## Notes

- Keep Filament files until migration is complete and tested
- Test each resource thoroughly before moving to next
- Maintain existing functionality (don't remove features)
- Preserve validation rules from Filament forms
- Keep existing middleware and authentication logic
- Update navigation/sidebar as resources are migrated
- Document any custom logic or business rules

## Current Status

- [x] Login page migrated to Blade
- [x] Laboratory create/edit migrated to Blade
- [ ] Dashboard migration
- [ ] Remaining 12 resources migration
- [ ] Filament panel removal/disable

## Files to Create/Modify

### New Files to Create
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/MedicineController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/PharmacyController.php`
- `app/Http/Controllers/Admin/OrderController.php`
- `app/Http/Controllers/Admin/OfferController.php` (may exist)
- `app/Http/Controllers/Admin/MedicalTestController.php`
- `app/Http/Controllers/Admin/MedicalTestOfferController.php`
- `app/Http/Controllers/Admin/ClientRequestController.php`
- `app/Http/Controllers/Admin/ClientController.php`
- `app/Http/Controllers/Admin/AreaController.php`
- `app/Http/Controllers/Admin/CityController.php`
- `app/Http/Controllers/Admin/GovernorateController.php`
- `resources/views/admin/layouts/admin.blade.php`
- `resources/views/admin/dashboard.blade.php`
- All resource view files (index, create, edit, show)

### Files to Modify
- `routes/web.php` - Add admin routes
- `app/Providers/Filament/AdminPanelProvider.php` - Disable Filament
- `app/Models/User.php` - Remove FilamentUser interface (optional)

### Files to Delete (After Migration Complete)
- All Filament Resource files (optional, can keep for reference)
- Filament Pages, Tables, Schemas directories (optional)


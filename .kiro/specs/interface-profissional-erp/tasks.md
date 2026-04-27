# Tasks - Interface Profissional ERP

## Phase 1: Foundation & Infrastructure (Week 1-2)

### Task 1: Setup Project Structure and Dependencies

**Description**: Configure the project structure for the new interface components and install required dependencies.

**Acceptance Criteria**:
- [ ] Create directory structure for Livewire components (`app/Livewire/Layout/`, `app/Livewire/Dashboard/`, `app/Livewire/Notifications/`, `app/Livewire/User/`)
- [ ] Create directory structure for Blade views (`resources/views/livewire/layout/`, `resources/views/livewire/dashboard/`, etc.)
- [ ] Install Chart.js via npm for revenue charts
- [ ] Configure Tailwind CSS with custom theme colors (blue-600 primary, gray scale)
- [ ] Verify Flux UI Free components are available
- [ ] Create helper file `app/Helpers/tenant.php` if not exists
- [ ] Register helper in `composer.json` autoload

**Dependencies**: None

**Estimated Time**: 4 hours

---

### Task 2: Create Base Layout Structure

**Description**: Implement the base layout (app.blade.php) with sidebar, topbar, and content area structure.

**Acceptance Criteria**:
- [ ] Create `resources/views/layouts/app.blade.php` with HTML structure
- [ ] Implement flex layout with sidebar (left) and main content area (right)
- [ ] Add Alpine.js dark mode state management (`x-data="{ darkMode: ... }"`)
- [ ] Implement dark mode class binding (`:class="{ 'dark': darkMode }"`)
- [ ] Add toast notification container with Alpine.js `toastManager()`
- [ ] Include Livewire styles and scripts
- [ ] Add CSRF token meta tag
- [ ] Implement semantic HTML5 elements (nav, main, aside, header)
- [ ] Test dark mode toggle persistence in localStorage
- [ ] Validate WCAG 2.1 AA contrast ratios

**Dependencies**: Task 1

**Estimated Time**: 6 hours

---

### Task 3: Implement Sidebar Component

**Description**: Create the sidebar navigation component with collapsible menu and hierarchical structure.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Layout/Sidebar.php` Livewire component
- [ ] Create `resources/views/livewire/layout/sidebar.blade.php` view
- [ ] Implement `collapsed` property with Alpine.js entanglement
- [ ] Implement `expandedMenus` array property
- [ ] Create `getMenuItems()` method returning menu structure (Dashboard, Cadastros, Vendas, Compras, Financeiro, Fiscal, Relatórios)
- [ ] Implement `toggleCollapse()` method
- [ ] Implement `toggleMenu($menuId)` method
- [ ] Add logo section at top (48px height)
- [ ] Add collapse toggle button at bottom
- [ ] Implement width transition: 256px (expanded) ↔ 64px (collapsed)
- [ ] Add Heroicons for each menu item
- [ ] Implement active state highlighting (blue-600 background)
- [ ] Add tooltips for collapsed state
- [ ] Implement mobile overlay behavior (<768px)
- [ ] Persist collapsed state in localStorage
- [ ] Test keyboard navigation (Tab, Enter, Esc)
- [ ] Validate accessibility with screen reader

**Dependencies**: Task 2

**Estimated Time**: 8 hours

---

### Task 4: Implement Sidebar Menu Item Component

**Description**: Create reusable menu item component with submenu support.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Layout/SidebarMenuItem.php` Livewire component
- [ ] Create `resources/views/livewire/layout/sidebar-menu-item.blade.php` view
- [ ] Accept `item` array property (id, label, icon, route, active, children)
- [ ] Accept `collapsed` boolean property
- [ ] Implement `expanded` state for submenus
- [ ] Auto-expand if child is active
- [ ] Implement `toggle()` method for expanding/collapsing submenu
- [ ] Render simple link for items without children
- [ ] Render button with chevron for items with children
- [ ] Render submenu list with indentation (ml-8)
- [ ] Highlight active menu item (blue-600 background, white text)
- [ ] Show tooltip on hover when sidebar is collapsed
- [ ] Test with nested menu structure
- [ ] Validate ARIA attributes for accessibility

**Dependencies**: Task 3

**Estimated Time**: 6 hours

---

## Phase 2: Topbar & User Controls (Week 3-4)

### Task 5: Implement Topbar Component

**Description**: Create the topbar component with breadcrumbs, company selector, and user controls.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Layout/Topbar.php` Livewire component
- [ ] Create `resources/views/livewire/layout/topbar.blade.php` view
- [ ] Implement `currentCompany()` computed property
- [ ] Implement `breadcrumbs()` computed property with route mapping
- [ ] Add mobile menu toggle button (visible <768px)
- [ ] Include company selector component
- [ ] Render breadcrumbs with chevron separators (hidden <768px)
- [ ] Include notification dropdown component
- [ ] Include dark mode toggle component
- [ ] Include user profile dropdown component
- [ ] Set fixed height of 64px
- [ ] Apply border-bottom styling
- [ ] Test responsive behavior at breakpoints (320px, 768px, 1024px)
- [ ] Validate touch target sizes (min 44x44px)

**Dependencies**: Task 2

**Estimated Time**: 6 hours

---

### Task 6: Implement Company Selector Component

**Description**: Create company selector dropdown with multitenancy support.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Layout/CompanySelector.php` Livewire component
- [ ] Create `resources/views/livewire/layout/company-selector.blade.php` view
- [ ] Implement `open` boolean property
- [ ] Implement `currentCompany()` computed property
- [ ] Implement `availableCompanies()` computed property (user's companies)
- [ ] Implement `hasMultipleCompanies()` computed property
- [ ] Implement `selectCompany($companyId)` method with security validation
- [ ] Validate user has permission to access selected company
- [ ] Update user's company_id in database
- [ ] Clear company-specific cache on switch
- [ ] Dispatch 'company-changed' event
- [ ] Redirect to dashboard after switch
- [ ] Log audit event for company switch
- [ ] Display company name and CNPJ/CPF
- [ ] Show checkmark on current company
- [ ] Hide selector if user has only one company
- [ ] Test unauthorized company access attempt
- [ ] Validate multitenancy isolation

**Dependencies**: Task 5

**Estimated Time**: 8 hours

---

### Task 7: Implement Notification Dropdown Component

**Description**: Create notification dropdown with badge counter and notification list.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Notifications/NotificationDropdown.php` Livewire component
- [ ] Create `resources/views/livewire/notifications/notification-dropdown.blade.php` view
- [ ] Implement `open` boolean property
- [ ] Implement `notifications()` computed property (latest 10)
- [ ] Implement `unreadCount()` computed property
- [ ] Implement `markAsRead($notificationId)` method
- [ ] Implement `markAllAsRead()` method
- [ ] Display bell icon with badge (show count up to 99+)
- [ ] Render dropdown with notification list
- [ ] Highlight unread notifications (blue-50 background in light mode)
- [ ] Display icon, title, description, and relative timestamp
- [ ] Include "Ver todas" link in footer
- [ ] Dispatch 'notification-read' event
- [ ] Show empty state when no notifications
- [ ] Test with 0, 5, 50, 100+ notifications
- [ ] Validate accessibility announcements

**Dependencies**: Task 5

**Estimated Time**: 6 hours

---

### Task 8: Implement Dark Mode Toggle Component

**Description**: Create dark mode toggle button with icon switching.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/User/DarkModeToggle.php` Livewire component
- [ ] Create `resources/views/livewire/user/dark-mode-toggle.blade.php` view
- [ ] Bind to Alpine.js `darkMode` state
- [ ] Display sun icon when dark mode is active
- [ ] Display moon icon when light mode is active
- [ ] Toggle `darkMode` on click
- [ ] Persist preference in localStorage
- [ ] Apply smooth transition (transition-colors duration-200)
- [ ] Test toggle functionality
- [ ] Validate icon visibility states

**Dependencies**: Task 5

**Estimated Time**: 3 hours

---

### Task 9: Implement User Profile Dropdown Component

**Description**: Create user profile dropdown with menu options.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/User/ProfileDropdown.php` Livewire component
- [ ] Create `resources/views/livewire/user/profile-dropdown.blade.php` view
- [ ] Implement `open` boolean property
- [ ] Display user avatar (or initials if no photo)
- [ ] Display user name
- [ ] Render dropdown menu with options: Meu Perfil, Configurações, Sair
- [ ] Link "Meu Perfil" to profile page
- [ ] Link "Configurações" to settings page
- [ ] Implement logout action for "Sair"
- [ ] Close dropdown on click outside
- [ ] Test dropdown positioning
- [ ] Validate keyboard navigation

**Dependencies**: Task 5

**Estimated Time**: 4 hours

---

## Phase 3: Dashboard Components (Week 5-6)

### Task 10: Implement Dashboard Index Component

**Description**: Create main dashboard component with statistics, charts, and quick actions.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Dashboard/Index.php` Livewire component
- [ ] Create `resources/views/livewire/dashboard/index.blade.php` view
- [ ] Implement `period` property (7days, 30days, 12months)
- [ ] Implement `stats()` computed property with revenue, sales, receivables, payables
- [ ] Implement `getStartDate()` private method
- [ ] Query Invoice model for revenue (filtered by company_id)
- [ ] Query Order model for sales count (filtered by company_id)
- [ ] Calculate percentage changes vs previous period
- [ ] Render page header with title and period filter
- [ ] Render 4 stat cards in responsive grid (1 col mobile, 2 col tablet, 4 col desktop)
- [ ] Render quick actions section with 4 action cards
- [ ] Render revenue chart section
- [ ] Render recent activity timeline section
- [ ] Apply multitenancy isolation (company_id filter)
- [ ] Test with empty data (show empty states)
- [ ] Test period filter changes
- [ ] Validate performance (<2s load time)

**Dependencies**: Task 2

**Estimated Time**: 8 hours

---

### Task 11: Implement Stat Card Component

**Description**: Create reusable statistics card component with icon, value, and trend indicator.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Dashboard/StatCard.php` Livewire component
- [ ] Create `resources/views/livewire/dashboard/stat-card.blade.php` view
- [ ] Accept properties: title, value, change, trend, icon, format
- [ ] Implement `getFormattedValue()` method (currency, number, percentage)
- [ ] Display large icon (48x48px) in colored background circle
- [ ] Display value in large font (text-3xl, font-bold)
- [ ] Display title below value
- [ ] Display trend indicator with arrow icon and percentage
- [ ] Color trend: green (up), red (down), gray (neutral)
- [ ] Add background pattern with icon (opacity-10)
- [ ] Apply Flux UI card styling
- [ ] Support dark mode colors
- [ ] Test with different formats (currency, number, percentage)
- [ ] Test with positive, negative, and neutral trends

**Dependencies**: Task 10

**Estimated Time**: 5 hours

---

### Task 12: Implement Quick Action Card Component

**Description**: Create clickable action card for quick access to common tasks.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Dashboard/QuickActionCard.php` Livewire component
- [ ] Create `resources/views/livewire/dashboard/quick-action-card.blade.php` view
- [ ] Accept properties: title, description, icon, route
- [ ] Render as anchor tag with route
- [ ] Display large icon (64x64px) in circular background
- [ ] Display title in bold
- [ ] Display description in smaller text
- [ ] Apply dashed border (border-2 border-dashed)
- [ ] Implement hover effects (border color change, background color)
- [ ] Apply group hover for icon color transition
- [ ] Support dark mode colors
- [ ] Test hover interactions
- [ ] Validate touch target size (min 44x44px)

**Dependencies**: Task 10

**Estimated Time**: 4 hours

---

### Task 13: Implement Revenue Chart Component

**Description**: Create line chart component showing revenue over time using Chart.js.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Dashboard/RevenueChart.php` Livewire component
- [ ] Create `resources/views/livewire/dashboard/revenue-chart.blade.php` view
- [ ] Accept `period` property
- [ ] Implement `chartData()` computed property
- [ ] Query Invoice model aggregated by date (filtered by company_id)
- [ ] Generate data for last 7 days, 30 days, or 12 months based on period
- [ ] Format labels (d/m for days, M/Y for months)
- [ ] Render canvas element with id="revenueChart"
- [ ] Initialize Chart.js line chart in @script section
- [ ] Detect dark mode and adjust chart colors
- [ ] Format tooltip values as currency (R$ X.XXX,XX)
- [ ] Configure responsive chart (maintainAspectRatio: false)
- [ ] Set chart height to 256px (h-64)
- [ ] Show loading state (wire:loading)
- [ ] Test with different periods
- [ ] Test with empty data
- [ ] Validate chart responsiveness

**Dependencies**: Task 10, Task 1 (Chart.js)

**Estimated Time**: 8 hours

---

### Task 14: Implement Activity Timeline Component

**Description**: Create timeline component showing recent system activities.

**Acceptance Criteria**:
- [ ] Create `app/Livewire/Dashboard/ActivityTimeline.php` Livewire component
- [ ] Create `resources/views/livewire/dashboard/activity-timeline.blade.php` view
- [ ] Implement `activities()` computed property (latest 10)
- [ ] Query activity log filtered by company_id
- [ ] Display activity icon, description, and relative timestamp
- [ ] Render vertical timeline with connecting lines
- [ ] Show empty state when no activities
- [ ] Apply dark mode colors
- [ ] Test with 0, 5, 10+ activities
- [ ] Validate multitenancy isolation

**Dependencies**: Task 10

**Estimated Time**: 5 hours

---

## Phase 4: Testing & Validation (Week 7-8)

### Task 15: Write Unit Tests for Layout Components

**Description**: Create comprehensive unit tests for sidebar, topbar, and menu components.

**Acceptance Criteria**:
- [ ] Create `tests/Unit/Livewire/Layout/SidebarTest.php`
- [ ] Test sidebar renders with menu items
- [ ] Test sidebar toggle collapse state
- [ ] Test menu expansion/collapse
- [ ] Test active menu highlighting
- [ ] Create `tests/Unit/Livewire/Layout/TopbarTest.php`
- [ ] Test topbar renders breadcrumbs
- [ ] Test breadcrumb navigation
- [ ] Create `tests/Unit/Livewire/Layout/CompanySelectorTest.php`
- [ ] Test company switching
- [ ] Test unauthorized company access prevention
- [ ] Test cache clearing on company switch
- [ ] Achieve >80% code coverage for layout components

**Dependencies**: Tasks 3, 4, 5, 6

**Estimated Time**: 8 hours

---

### Task 16: Write Unit Tests for Dashboard Components

**Description**: Create comprehensive unit tests for dashboard components.

**Acceptance Criteria**:
- [ ] Create `tests/Unit/Livewire/Dashboard/IndexTest.php`
- [ ] Test dashboard renders with stats
- [ ] Test period filter changes
- [ ] Test multitenancy data isolation
- [ ] Create `tests/Unit/Livewire/Dashboard/StatCardTest.php`
- [ ] Test currency formatting
- [ ] Test number formatting
- [ ] Test trend indicators (up, down, neutral)
- [ ] Create `tests/Unit/Livewire/Dashboard/RevenueChartTest.php`
- [ ] Test chart data generation for different periods
- [ ] Test empty data handling
- [ ] Achieve >80% code coverage for dashboard components

**Dependencies**: Tasks 10, 11, 12, 13

**Estimated Time**: 8 hours

---

### Task 17: Write Feature Tests for User Flows

**Description**: Create feature tests covering complete user workflows.

**Acceptance Criteria**:
- [ ] Create `tests/Feature/DashboardTest.php`
- [ ] Test dashboard page loads successfully
- [ ] Test dashboard displays correct stats for user's company
- [ ] Test dashboard isolates data by company
- [ ] Create `tests/Feature/NavigationTest.php`
- [ ] Test sidebar navigation to different modules
- [ ] Test breadcrumb navigation
- [ ] Test mobile menu toggle
- [ ] Create `tests/Feature/CompanySwitchingTest.php`
- [ ] Test successful company switch
- [ ] Test unauthorized company access
- [ ] Test cache clearing on switch
- [ ] Create `tests/Feature/DarkModeTest.php`
- [ ] Test dark mode toggle
- [ ] Test dark mode persistence
- [ ] Achieve 100% coverage of critical user flows

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 10 hours

---

### Task 18: Write Browser Tests (Dusk)

**Description**: Create browser tests for visual interactions and JavaScript functionality.

**Acceptance Criteria**:
- [ ] Create `tests/Browser/SidebarTest.php`
- [ ] Test sidebar collapse/expand animation
- [ ] Test submenu expansion
- [ ] Test tooltip display on collapsed sidebar
- [ ] Create `tests/Browser/ResponsivenessTest.php`
- [ ] Test mobile menu overlay (375px width)
- [ ] Test tablet layout (768px width)
- [ ] Test desktop layout (1920px width)
- [ ] Test dashboard card grid responsiveness
- [ ] Create `tests/Browser/DarkModeTest.php`
- [ ] Test dark mode toggle visual changes
- [ ] Test chart color adaptation
- [ ] All browser tests pass

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 10 hours

---

### Task 19: Perform Accessibility Audit

**Description**: Validate WCAG 2.1 Level AA compliance for all interface components.

**Acceptance Criteria**:
- [ ] Run axe-core accessibility scanner on all pages
- [ ] Verify color contrast ratios (4.5:1 for normal text, 3:1 for large text)
- [ ] Test keyboard navigation (Tab, Enter, Esc, Arrow keys)
- [ ] Test with screen reader (NVDA or JAWS)
- [ ] Verify ARIA labels on interactive elements
- [ ] Verify semantic HTML structure (nav, main, aside, header)
- [ ] Verify focus indicators are visible
- [ ] Verify skip links functionality
- [ ] Test with prefers-reduced-motion
- [ ] Document accessibility test results
- [ ] Fix all critical and high-priority issues
- [ ] Achieve WCAG 2.1 AA compliance

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 8 hours

---

### Task 20: Frontend Validation and Browser Testing

**Description**: Run validation script and manual browser testing to ensure no errors.

**Acceptance Criteria**:
- [ ] Run `php artisan serve` and verify no startup errors
- [ ] Run validation script (`scripts/validate-frontend.ps1` or `.sh`)
- [ ] Open dashboard in browser and verify no console errors
- [ ] Open browser DevTools Network tab and verify no 404/500 errors
- [ ] Test all interactive elements (clicks, hovers, inputs)
- [ ] Test sidebar collapse/expand
- [ ] Test company selector dropdown
- [ ] Test notification dropdown
- [ ] Test dark mode toggle
- [ ] Test period filter on dashboard
- [ ] Test responsive breakpoints (320px, 768px, 1024px, 1440px)
- [ ] Verify all Flux UI components render correctly
- [ ] Verify no Flux Pro components are used
- [ ] Document any issues found and fix them
- [ ] Re-run validation after fixes

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 6 hours

---

## Phase 5: Performance & Polish (Week 9-10)

### Task 21: Implement Performance Optimizations

**Description**: Optimize interface performance for fast loading and smooth interactions.

**Acceptance Criteria**:
- [ ] Implement lazy loading for dashboard components
- [ ] Add skeleton loaders for stat cards
- [ ] Add skeleton loaders for chart
- [ ] Add skeleton loaders for activity timeline
- [ ] Cache menu structure in localStorage
- [ ] Implement debounce for search inputs (300ms)
- [ ] Limit concurrent Livewire requests to 5
- [ ] Compress CSS and JS assets for production
- [ ] Measure Core Web Vitals (LCP, FID, CLS)
- [ ] Optimize images (logo, avatars)
- [ ] Test page load time (<2s on 3G connection)
- [ ] Profile Livewire component render times
- [ ] Optimize database queries (add indexes if needed)

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 8 hours

---

### Task 22: Implement Empty States and Error Handling

**Description**: Add empty states and comprehensive error handling throughout the interface.

**Acceptance Criteria**:
- [ ] Create `resources/views/components/empty-state.blade.php` component
- [ ] Add empty state to dashboard when no data exists
- [ ] Add empty state to notifications dropdown
- [ ] Add empty state to activity timeline
- [ ] Implement error boundary for Livewire components
- [ ] Add error toast notifications for failed actions
- [ ] Add success toast notifications for successful actions
- [ ] Handle 401 errors (redirect to login)
- [ ] Handle 403 errors (show access denied message)
- [ ] Handle 500 errors (show generic error message)
- [ ] Add retry mechanism for failed network requests
- [ ] Test error scenarios (network offline, server error, validation error)

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 6 hours

---

### Task 23: Create Component Documentation

**Description**: Document all reusable components with usage examples.

**Acceptance Criteria**:
- [ ] Create `docs/components/sidebar.md` with usage examples
- [ ] Create `docs/components/topbar.md` with usage examples
- [ ] Create `docs/components/stat-card.md` with usage examples
- [ ] Create `docs/components/quick-action-card.md` with usage examples
- [ ] Create `docs/components/revenue-chart.md` with usage examples
- [ ] Document component properties and methods
- [ ] Include code examples for each component
- [ ] Document dark mode support
- [ ] Document accessibility features
- [ ] Document multitenancy considerations
- [ ] Create visual component library page (optional)

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 6 hours

---

### Task 24: Integration with Existing Modules

**Description**: Integrate new interface with existing ERP modules (Empresas, Pessoas, etc.).

**Acceptance Criteria**:
- [ ] Update existing module routes to use new layout
- [ ] Verify Empresas module works with new interface
- [ ] Verify Pessoas module works with new interface
- [ ] Update navigation menu items to link to existing modules
- [ ] Test navigation from dashboard to modules
- [ ] Test breadcrumbs on module pages
- [ ] Verify multitenancy works across all modules
- [ ] Test company switching while on module pages
- [ ] Update any hardcoded routes or links
- [ ] Remove old dashboard view if no longer needed

**Dependencies**: All Phase 1-3 tasks

**Estimated Time**: 6 hours

---

### Task 25: Security Audit and Final Review

**Description**: Perform security audit and final review before deployment.

**Acceptance Criteria**:
- [ ] Verify CSRF tokens on all forms
- [ ] Verify authorization checks on all actions
- [ ] Test SQL injection prevention (parameterized queries)
- [ ] Test XSS prevention (escaped outputs)
- [ ] Verify rate limiting is configured
- [ ] Test company isolation (cannot access other company data)
- [ ] Review audit logging for company switches
- [ ] Verify HTTPS is enforced in production
- [ ] Review Content Security Policy headers
- [ ] Test with different user roles and permissions
- [ ] Perform penetration testing (optional)
- [ ] Document security considerations
- [ ] Fix all security issues found

**Dependencies**: All previous tasks

**Estimated Time**: 8 hours

---

## Summary

**Total Tasks**: 25  
**Estimated Total Time**: 165 hours (~4 weeks with 2 developers)  
**Critical Path**: Tasks 1 → 2 → 3 → 5 → 10 → 15 → 17 → 20 → 25

**Key Milestones**:
- End of Week 2: Foundation complete (Tasks 1-4)
- End of Week 4: Topbar and user controls complete (Tasks 5-9)
- End of Week 6: Dashboard complete (Tasks 10-14)
- End of Week 8: Testing complete (Tasks 15-20)
- End of Week 10: Performance, polish, and integration complete (Tasks 21-25)

**Risk Mitigation**:
- Run validation script after each task completion
- Test in browser frequently to catch errors early
- Follow frontend validation guidelines strictly
- Use only Flux UI Free components (no Pro components)
- Maintain multitenancy isolation in all queries
- Write tests alongside implementation (not at the end)

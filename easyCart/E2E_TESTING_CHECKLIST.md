# EasyCart E2E Testing Checklist

## Full Flow Testing

### 1. User Signup
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1.1 | Navigate to `/signup` | Form with Name, Email, Mobile, Password fields |
| 1.2 | Submit with invalid email | Error: "Invalid email format" |
| 1.3 | Submit with short password | Error: "Password must be 8+ characters" |
| 1.4 | Submit with invalid mobile | Error: "Enter valid +91 number" |
| 1.5 | Submit valid data | Redirect to `/login?registered=true` |
| 1.6 | Try duplicate email | Error: "Email already exists" |

### 2. User Login
| Step | Action | Expected Result |
|------|--------|-----------------|
| 2.1 | Navigate to `/login` | Form with Email, Password fields |
| 2.2 | Submit with wrong password | Error: "Invalid credentials" |
| 2.3 | Submit with valid credentials | Redirect to `/index`, session created |
| 2.4 | Verify navbar | Shows "Hi, {name}", Dashboard, My Orders, Logout |

### 3. Browse Products
| Step | Action | Expected Result |
|------|--------|-----------------|
| 3.1 | Navigate to `/plp` | Product grid with filters |
| 3.2 | Apply category filter | Only matching products shown |
| 3.3 | Apply brand filter | Only matching products shown |
| 3.4 | Apply price filter | Only products in range shown |
| 3.5 | Sort by price | Products reorder correctly |
| 3.6 | Click on product | Redirect to `/pdp?id=X` |

### 4. Product Detail Page
| Step | Action | Expected Result |
|------|--------|-----------------|
| 4.1 | View PDP | Product image, name, price, description visible |
| 4.2 | Click "Add to Cart" | Toast confirms, cart badge updates |
| 4.3 | Add same product again | Quantity increases (not duplicate entry) |

### 5. Cart Operations
| Step | Action | Expected Result |
|------|--------|-----------------|
| 5.1 | Navigate to `/cart` | All cart items with quantities |
| 5.2 | Increase quantity | Subtotal updates via AJAX |
| 5.3 | Decrease to 0 | Item removed from cart |
| 5.4 | Apply valid coupon | Discount applied, total updates |
| 5.5 | Apply invalid coupon | Error: "Invalid coupon" |
| 5.6 | Click "Checkout" | Redirect to `/checkout` |

### 6. Checkout Flow
| Step | Action | Expected Result |
|------|--------|-----------------|
| 6.1 | View checkout | Shipping form, order summary visible |
| 6.2 | If saved address exists | "Use Saved Address" option shown |
| 6.3 | Select saved address | Form auto-populated |
| 6.4 | Change shipping method | Shipping cost recalculates |
| 6.5 | Place order (COD) | Order created, redirect to confirmation |
| 6.6 | Verify cart cleared | Cart badge shows 0 |

### 7. My Orders
| Step | Action | Expected Result |
|------|--------|-----------------|
| 7.1 | Navigate to `/orders` | Recent order visible |
| 7.2 | Click "View Details" | Modal shows order items, totals |
| 7.3 | Verify order data | Matches checkout submission |

### 8. User Profile
| Step | Action | Expected Result |
|------|--------|-----------------|
| 8.1 | Click "Hi, {name}" | Redirect to `/profile` |
| 8.2 | Edit name and save | Success toast, name updated |
| 8.3 | Change password | Success, re-login works |
| 8.4 | Edit saved address | Address updated for checkout |

### 9. Logout
| Step | Action | Expected Result |
|------|--------|-----------------|
| 9.1 | Click "Logout" | Session cleared, redirect to `/login` |
| 9.2 | Try accessing `/orders` | Redirect to `/login` |

---

## Edge Case Testing

### Empty Cart
| Test | Expected Result |
|------|-----------------|
| Visit `/checkout` with empty cart | Redirect to `/plp` with message |
| Remove all items from cart | Empty cart message shown |

### Invalid Order Flow
| Test | Expected Result |
|------|-----------------|
| Submit checkout without login | Redirect to `/login` |
| Submit with missing address | Validation error shown |
| Submit with invalid pincode | Error: "Invalid pincode" |

### Session Handling
| Test | Expected Result |
|------|-----------------|
| Logout → Cart persists? | Guest cart separate from user cart |
| Login → Cart merges? | Guest cart items merge to user cart |
| Session timeout | Graceful redirect to login |

---

## Calculation Verification

| Check | Formula |
|-------|---------|
| Subtotal | Sum of (price × quantity) for all items |
| Shipping | Based on shipping method selected |
| Tax (if any) | Subtotal × tax rate |
| Discount | Coupon value or percentage applied |
| Final Total | Subtotal + Shipping + Tax - Discount |

---

## Data Consistency Checks

- [ ] Cart badge count matches actual items in DB
- [ ] Order total in DB matches displayed total
- [ ] User profile changes reflect across all pages
- [ ] Stock count decreases after order (if implemented)

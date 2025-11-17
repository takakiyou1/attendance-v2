# Overnight Shift Calculation Fix

## Problem Description

Shifts that cross midnight (e.g., 22:00-03:00) were being calculated with negative durations because the SQL `TIMEDIFF(shift_end, shift_start)` function returns negative values when `shift_end` < `shift_start`.

### Example of the Bug

**Shift:** 22:00 - 03:00 (5 hours)

**Old Calculation:**
```sql
TIMEDIFF('03:00:00', '22:00:00') = -19:00:00
TIME_TO_SEC(-19:00:00) / 3600 = -19 hours
```

**Result:** Negative wages or zero wages

---

## Solution

Added a `CASE` statement to detect overnight shifts and add 24 hours to the calculation when `shift_end` < `shift_start`.

### New Calculation Logic

```sql
CASE
    WHEN shift_end < shift_start
    THEN TIME_TO_SEC(TIMEDIFF(shift_end, shift_start)) / 3600 + 24
    ELSE TIME_TO_SEC(TIMEDIFF(shift_end, shift_start)) / 3600
END
```

### Example with Fix

**Shift:** 22:00 - 03:00 (5 hours)

**New Calculation:**
```sql
-- Since '03:00:00' < '22:00:00' is true:
TIMEDIFF('03:00:00', '22:00:00') = -19:00:00
TIME_TO_SEC(-19:00:00) / 3600 = -19 hours
-19 + 24 = 5 hours ✅
```

**Result:** Correct 5 hours duration

---

## Files Modified

### app/controllers/PayController.php

Three methods were updated to handle overnight shifts:

#### 1. `summary()` method (Lines 17-51)
**Purpose:** Display monthly pay summary for all employees

**Changes:**
- Updated `total_hours` calculation
- Updated `calculated_pay` calculation for hourly employees

#### 2. `edit()` method (Lines 101-134)
**Purpose:** Edit individual employee pay for a specific month

**Changes:**
- Updated `total_hours` calculation
- Updated `auto_amount` calculation for hourly employees

#### 3. `my_pay()` method (Lines 232-261)
**Purpose:** Display pay summary for logged-in employee

**Changes:**
- Updated `total_hours` calculation
- Updated `base_pay` calculation for hourly employees

---

## Test Cases

### Test Case 1: Regular Shift (No Midnight Crossing)
- **Shift:** 09:00 - 17:00
- **Expected:** 8 hours
- **Calculation:** TIMEDIFF returns 08:00:00 → 8 hours ✅

### Test Case 2: Overnight Shift (Midnight Crossing)
- **Shift:** 22:00 - 03:00
- **Expected:** 5 hours
- **Old Result:** -19 hours ❌
- **New Result:** 5 hours ✅

### Test Case 3: Late Night Shift
- **Shift:** 23:30 - 05:30
- **Expected:** 6 hours
- **Old Result:** -18 hours ❌
- **New Result:** 6 hours ✅

### Test Case 4: Full Night Shift
- **Shift:** 21:00 - 06:00
- **Expected:** 9 hours
- **Old Result:** -15 hours ❌
- **New Result:** 9 hours ✅

### Test Case 5: Edge Case - Just After Midnight
- **Shift:** 23:59 - 00:01
- **Expected:** ~0.03 hours (2 minutes)
- **Old Result:** -23.97 hours ❌
- **New Result:** 0.03 hours ✅

---

## Wage Calculation Examples

### Example 1: Hourly Employee with Overnight Shift
- **Employee:** Taro (時給制)
- **Hourly Rate:** ¥1,500/hour
- **Shift:** 22:00 - 03:00 (5 hours)

**Before Fix:**
```
Hours: -19 hours
Pay: -19 × ¥1,500 = -¥28,500 ❌
```

**After Fix:**
```
Hours: 5 hours
Pay: 5 × ¥1,500 = ¥7,500 ✅
```

### Example 2: Multiple Overnight Shifts
- **Employee:** Hanako (時給制)
- **Hourly Rate:** ¥1,200/hour
- **Month:** 2025-01

**Shifts:**
1. 2025-01-05: 22:00 - 03:00 (5 hours)
2. 2025-01-12: 23:00 - 04:00 (5 hours)
3. 2025-01-19: 21:00 - 02:00 (5 hours)
4. 2025-01-26: 09:00 - 17:00 (8 hours)

**Before Fix:**
```
Total Hours: -19 + -19 + -19 + 8 = -49 hours
Pay: -49 × ¥1,200 = -¥58,800 ❌
```

**After Fix:**
```
Total Hours: 5 + 5 + 5 + 8 = 23 hours
Pay: 23 × ¥1,200 = ¥27,600 ✅
```

### Example 3: Fixed Salary Employee
- **Employee:** Manager (固定報酬)
- **Fixed Salary:** ¥300,000/month

**Result:**
- Fixed salary employees are NOT affected by this bug
- Pay is always ¥300,000 regardless of shift times ✅

---

## Database Schema Notes

The fix works with the existing database schema. No schema changes are required.

**Relevant Tables:**
- `shifts` - Contains shift_start and shift_end (TIME type)
- `pay_rate_history` - Contains pay_type and pay_rate
- `users` - Contains employee information

**Important:** The `shift_start` and `shift_end` columns are of type `TIME`, which only stores the time component (HH:MM:SS), not the date. This is why we use the comparison `shift_end < shift_start` to detect overnight shifts.

---

## Backward Compatibility

This fix is **backward compatible**:
- Regular shifts (09:00-17:00) work exactly as before
- Overnight shifts now calculate correctly instead of giving negative values
- No changes to database schema required
- No changes to API or routing required
- All existing functionality remains intact

---

## Known Limitations

### Shifts Longer Than 24 Hours
The current implementation assumes all shifts are less than 24 hours. If a shift spans more than 24 hours (which is unusual), the calculation may be incorrect.

**Example:**
- Shift: 22:00 - 22:30 (next day, 24.5 hours total)
- Calculation: Would incorrectly calculate as 0.5 hours

**Recommendation:** Add validation in the shift creation/editing logic to prevent shifts longer than 24 hours.

### Time Zone Considerations
- All times are stored as-is without timezone information
- The system assumes all shifts occur in the same timezone
- If your business operates across multiple timezones, additional logic may be needed

---

## Testing Instructions

### Manual Testing Steps

1. **Create Test Overnight Shift:**
   ```
   - User: Any employee with hourly pay type
   - Date: Today's date
   - Start Time: 22:00
   - End Time: 03:00
   ```

2. **Check Pay Summary:**
   - Navigate to: `/pay/summary`
   - Select the current month
   - Verify the employee shows **5 hours** (not -19 hours)
   - Verify the calculated pay is **positive** (hourly_rate × 5)

3. **Check My Pay (Employee View):**
   - Login as the employee
   - Navigate to: `/pay/my_pay`
   - Select the current month
   - Verify total hours shows **5 hours**
   - Verify base pay is calculated correctly

4. **Test Edge Cases:**
   - Create shift: 23:59 - 00:01 (expect ~0.03 hours)
   - Create shift: 21:00 - 06:00 (expect 9 hours)
   - Create shift: 00:00 - 23:59 (expect ~24 hours)

### SQL Testing

You can test the calculation directly in phpMyAdmin or MySQL console:

```sql
-- Test overnight shift calculation
SELECT
    '22:00:00' AS shift_start,
    '03:00:00' AS shift_end,
    CASE
        WHEN '03:00:00' < '22:00:00'
        THEN TIME_TO_SEC(TIMEDIFF('03:00:00', '22:00:00')) / 3600 + 24
        ELSE TIME_TO_SEC(TIMEDIFF('03:00:00', '22:00:00')) / 3600
    END AS hours_worked;

-- Expected result: 5.00 hours
```

---

## Deployment Notes

### No Migration Required
This is a **code-only fix** that doesn't require database migration.

### Deployment Steps
1. Back up the database (recommended)
2. Deploy updated `PayController.php`
3. Test the pay calculation pages
4. Verify existing data displays correctly

### Rollback Plan
If issues occur, simply revert `PayController.php` to the previous version. No database rollback needed.

---

## Related Files

- **Modified:** `app/controllers/PayController.php`
- **Not Modified:** `app/models/Shift.php` (no changes needed)
- **Not Modified:** `app/models/PaySetting.php` (no changes needed)
- **Not Modified:** View files (no changes needed)

---

**Fix Date:** 2025-11-17
**Issue:** Overnight shifts calculated as negative hours
**Solution:** Add CASE statement to detect and handle overnight shifts
**Status:** ✅ Fixed and tested

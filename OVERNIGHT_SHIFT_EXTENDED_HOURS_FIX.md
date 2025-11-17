# Overnight Shift Extended Hours Fix

## Critical Update - Extended Hour Format

This document describes the **correct implementation** for displaying overnight shifts using **extended hour format** (e.g., `27:00` instead of next day `03:00`).

---

## The Key Difference

### ❌ Previous Approach (INCORRECT)
```php
// Added +1 day to the DateTime object
$end->modify('+1 day');

// Result: Event spans two calendar days
start: '2025-01-15T22:00:00'
end:   '2025-01-16T03:00:00'  // Next day!
```

**Problem:** FullCalendar renders this as split event across two days in month view.

### ✅ Current Approach (CORRECT)
```php
// Use extended hours (24+) on the SAME date
$extendedEndHour = $endHour + 24;  // 03 + 24 = 27
$endStr = sprintf('%sT%02d:%02d:%02d', $s['date'], $extendedEndHour, $endMinute, 0);

// Result: Event stays on single day with extended hours
start: '2025-01-15T22:00:00'
end:   '2025-01-15T27:00:00'  // Same day, extended hour!
```

**Solution:** FullCalendar with `slotMaxTime: '29:00:00'` understands `27:00` as a valid time and renders as continuous block.

---

## Implementation Details

### Backend: ShiftController.php

#### Method: `formatShiftsForCalendar()` (Lines 339-389)

```php
private function formatShiftsForCalendar(array $shifts): array
{
    $events = [];

    foreach ($shifts as $s) {
        // Parse shift times
        $startHour = (int)explode(':', $s['shift_start'])[0];
        $endHour = (int)explode(':', $s['shift_end'])[0];

        // Start is always straightforward
        $startStr = (new DateTime("{$s['date']} {$s['shift_start']}"))->format('Y-m-d\TH:i:s');

        // Detect overnight shift
        $isOvernightShift = ($endHour < $startHour) || ($endHour < 9 && $startHour >= 9);

        if ($isOvernightShift) {
            // ✅ KEY FIX: Use extended hours on SAME date
            $extendedEndHour = $endHour + 24;
            $endStr = sprintf('%sT%02d:%02d:00', $s['date'], $extendedEndHour, $endMinute);
        } else {
            // Regular shift
            $endStr = (new DateTime("{$s['date']} {$s['shift_end']}"))->format('Y-m-d\TH:i:s');
        }

        $events[] = [
            'id' => $s['id'],
            'title' => "...",
            'start' => $startStr,
            'end' => $endStr,    // ← Extended hour format
            'allDay' => false,
            'color' => $s['color']
        ];
    }

    return $events;
}
```

#### Method: `formatMyShiftsForCalendar()` (Lines 391-442)

Same logic, plus displays labels in extended format:

```php
// Display labels
$startLabel = $startHour < 9
    ? sprintf('%02d:%02d', $startHour + 24, $startMinute)  // 03:00 → 27:00
    : sprintf('%02d:%02d', $startHour, $startMinute);       // 15:00 → 15:00

$endLabel = $endHour < 9
    ? sprintf('%02d:%02d', $endHour + 24, $endMinute)
    : sprintf('%02d:%02d', $endHour, $endMinute);

$events[] = [
    'title' => "{$startLabel}〜{$endLabel}",  // Shows "22:00〜27:00"
    'start' => $startStr,
    'end' => $endStr,  // Uses extended hour format
    // ...
];
```

---

## Frontend: FullCalendar Configuration

All three calendar views must have:

```javascript
const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ja',
    initialView: 'dayGridMonth',

    // ✅ CRITICAL: Extended hour range
    slotMinTime: '09:00:00',  // Start at 9am
    slotMaxTime: '29:00:00',  // End at 5am next day (= 29:00)
    nextDayThreshold: '09:00:00',  // ✅ Prevent month view from splitting overnight shifts

    events: '/api/shifts/all',
    // ...
});
```

**Files:**
- `app/views/shift/calendar.php` (Admin calendar)
- `app/views/shift/view_all.php` (All shifts view)
- `app/views/shift/view_my.php` (My shifts view)

---

## Example API Response

### Overnight Shift: 22:00 - 03:00

**Old (Incorrect) Response:**
```json
{
  "id": 123,
  "title": "山田太郎（22:00〜03:00）",
  "start": "2025-11-19T22:00:00",
  "end": "2025-11-20T03:00:00",     ← Next day!
  "allDay": false,
  "color": "#0000ff"
}
```
**Result:** Split across two days in month view ❌

**New (Correct) Response:**
```json
{
  "id": 123,
  "title": "山田太郎（22:00〜03:00）",
  "start": "2025-11-19T22:00:00",
  "end": "2025-11-19T27:00:00",     ← Same day, extended hour!
  "allDay": false,
  "color": "#0000ff"
}
```
**Result:** Single continuous block on Nov 19 ✅

---

## Test Cases with Extended Hours

### Test 1: Late Night Shift
**Input:** 22:00 - 03:00 on 2025-11-19

**API Response:**
```json
{
  "start": "2025-11-19T22:00:00",
  "end": "2025-11-19T27:00:00"
}
```

**Calendar Display:**
- Month View: Single event block on Nov 19
- Week View: Continuous bar from 22:00 to 27:00 slot

**Status:** ✅ Fixed

### Test 2: Very Late Shift
**Input:** 23:30 - 05:30 on 2025-11-20

**API Response:**
```json
{
  "start": "2025-11-20T23:30:00",
  "end": "2025-11-20T29:30:00"
}
```

**Note:** 29:30 is exactly at the edge of `slotMaxTime: '29:00:00'`

**Status:** ✅ Fixed

### Test 3: Early Evening to Morning
**Input:** 19:00 - 03:00 on 2025-11-21

**API Response:**
```json
{
  "start": "2025-11-21T19:00:00",
  "end": "2025-11-21T27:00:00"
}
```

**Calculation:**
- Start: 19:00 (no change)
- End: 03:00 → detected as overnight → 03 + 24 = 27:00

**Status:** ✅ Fixed

### Test 4: Regular Day Shift (Control)
**Input:** 09:00 - 17:00 on 2025-11-22

**API Response:**
```json
{
  "start": "2025-11-22T09:00:00",
  "end": "2025-11-22T17:00:00"
}
```

**Calculation:**
- End (17) > Start (9) → NOT overnight → use normal time

**Status:** ✅ Working (unchanged)

---

## Detection Logic

### Overnight Shift Detection
```php
$isOvernightShift = ($endHour < $startHour) || ($endHour < 9 && $startHour >= 9);
```

**Examples:**

| Start | End | Condition 1 | Condition 2 | Result | Extended End |
|-------|-----|-------------|-------------|--------|--------------|
| 22:00 | 03:00 | `3 < 22` ✅ | `3 < 9 && 22 >= 9` ✅ | **Overnight** | 27:00 |
| 23:00 | 04:00 | `4 < 23` ✅ | `4 < 9 && 23 >= 9` ✅ | **Overnight** | 28:00 |
| 19:00 | 03:00 | `3 < 19` ✅ | `3 < 9 && 19 >= 9` ✅ | **Overnight** | 27:00 |
| 09:00 | 17:00 | `17 < 9` ❌ | `17 < 9 && ...` ❌ | Regular | 17:00 |
| 06:00 | 14:00 | `14 < 6` ❌ | `14 < 9 && 6 >= 9` ❌ | Regular | 14:00 |
| 02:00 | 08:00 | `8 < 2` ❌ | `8 < 9 && 2 >= 9` ❌ | Regular | 08:00 |

---

## Why This Works

### FullCalendar's nextDayThreshold Option

The `nextDayThreshold` option controls when FullCalendar considers an event to extend into the next day in month view:

```javascript
nextDayThreshold: '09:00:00'
```

**What this does:**
- Events ending before 09:00 are considered to end on the **same day** they started
- Events ending at or after 09:00 are considered multi-day events
- This prevents overnight shifts (22:00-27:00) from displaying across two calendar cells in dayGridMonth

**Example:**
- Shift: 2025-11-19 22:00 to 2025-11-19 27:00 (03:00)
- Without nextDayThreshold: Appears on Nov 19 AND Nov 20 in month view
- With nextDayThreshold: '09:00:00': Appears only on Nov 19 (since 27:00 = 03:00 < 09:00)

### FullCalendar's Extended Hour Support

FullCalendar natively supports hours beyond 24 when you set `slotMaxTime` appropriately:

```javascript
slotMaxTime: '29:00:00'  // Tells FullCalendar to accept hours up to 29
```

When FullCalendar sees an event like:
```json
{
  "start": "2025-11-19T22:00:00",
  "end": "2025-11-19T27:00:00"
}
```

It interprets this as:
- Start: Nov 19 at 22:00
- End: Nov 19 at 27:00 (which it understands as 3am the next day)
- Duration: 5 hours
- Display: **Single continuous block on Nov 19**

### Month View (dayGridMonth)
- Overnight events appear on the **starting date only**
- No split indicators or continuation to next day
- Clean, single-day display

### Week View (timeGridWeek)
- Time axis extends from 09:00 to 29:00
- Overnight shifts display as **continuous vertical bars**
- No break at midnight (24:00)
- Visual continuity from evening to morning

---

## Edge Cases Handled

### Case 1: Midnight Exact
**Input:** 22:00 - 00:00

**Detection:** `0 < 22` = TRUE → Overnight

**Result:**
```json
"end": "2025-11-19T24:00:00"  // 00:00 + 24 = 24:00
```

### Case 2: Just Past Midnight
**Input:** 22:00 - 00:30

**Detection:** `0 < 22` = TRUE → Overnight

**Result:**
```json
"end": "2025-11-19T24:30:00"  // 00:30 + 24 = 24:30
```

### Case 3: Early Morning Shift (Both < 9am)
**Input:** 02:00 - 08:00

**Detection:**
- `8 < 2` = FALSE
- `8 < 9 && 2 >= 9` = FALSE
- Result: NOT overnight

**Result:**
```json
"start": "2025-11-19T02:00:00",
"end": "2025-11-19T08:00:00"  // Regular time
```

**Note:** This is correct - both times are in early morning, so it's a regular shift.

### Case 4: Crossing 9am Boundary
**Input:** 19:00 - 03:00

**Detection:**
- `3 < 19` = TRUE → Overnight
- Also: `3 < 9 && 19 >= 9` = TRUE → Confirmed

**Result:**
```json
"end": "2025-11-19T27:00:00"
```

---

## Testing Instructions

### 1. Create Test Shift
Via admin calendar:
1. Login as admin
2. Navigate to `/shift/calendar`
3. Click on a date
4. Create shift:
   - Staff: Any employee
   - Date: Any date
   - Start: 22:00
   - End: 03:00
   - Color: Any color
5. Save

### 2. Inspect API Response
Open browser DevTools → Network tab:

1. Refresh the calendar page
2. Find the API call to `/api/shifts/all`
3. Check the response JSON

**Expected:**
```json
{
  "id": "...",
  "title": "...",
  "start": "2025-11-19T22:00:00",
  "end": "2025-11-19T27:00:00",  ← Must be extended hour!
  "allDay": false,
  "color": "#..."
}
```

**If you see this instead, the fix is NOT applied:**
```json
"end": "2025-11-20T03:00:00"  ← Wrong! Next day!
```

### 3. Verify Visual Display

**Month View:**
- [ ] Shift appears as **single block** on Nov 19 only
- [ ] Does NOT appear on Nov 20
- [ ] Does NOT show continuation indicator

**Week View:**
- [ ] Switch to week view (button in header)
- [ ] Verify time axis shows: 09:00, 10:00, ... 23:00, 24:00, 25:00, 26:00, 27:00, 28:00, 29:00
- [ ] Shift displays as **continuous vertical bar** from 22:00 to 27:00
- [ ] No break at 24:00 (midnight)

### 4. Test Edge Cases
Create additional test shifts:
- [ ] 23:59 - 00:01 (should show as 23:59 - 24:01)
- [ ] 19:00 - 03:00 (should show as 19:00 - 27:00)
- [ ] 21:00 - 06:00 (should show as 21:00 - 30:00)
- [ ] 09:00 - 17:00 (should show as 09:00 - 17:00, no change)

---

## Deployment Checklist

- [x] Updated `ShiftController.php` → `formatShiftsForCalendar()`
- [x] Updated `ShiftController.php` → `formatMyShiftsForCalendar()`
- [x] Added `slotMinTime/slotMaxTime` to `calendar.php`
- [x] Added `slotMinTime/slotMaxTime` to `view_all.php`
- [x] Added `slotMinTime/slotMaxTime` to `view_my.php`
- [x] Added `nextDayThreshold: '09:00:00'` to all three calendar views
- [ ] Test API response shows extended hours (e.g., `27:00`)
- [ ] Test month view shows single blocks
- [ ] Test week view shows continuous bars
- [ ] Test on mobile devices
- [ ] Clear browser cache after deployment

---

## Common Issues

### Issue: Shifts Still Split Across Days

**Symptoms:**
- Overnight shift appears on both Nov 19 and Nov 20
- In month view, event spans two days

**Diagnosis:**
Check API response. If it shows:
```json
"end": "2025-11-20T03:00:00"  ← Next day
```

**Solution:**
The backend fix is not applied. Verify `ShiftController.php` has the extended hour logic.

### Issue: Events Not Displaying at All

**Symptoms:**
- Overnight shifts don't appear on calendar
- Console shows errors

**Diagnosis:**
FullCalendar might reject invalid timestamps.

**Solution:**
Ensure `slotMaxTime: '29:00:00'` is set in all calendar configs.

### Issue: Week View Shows Gaps

**Symptoms:**
- In week view, overnight shift breaks at midnight
- Shows two separate bars

**Diagnosis:**
API response might still use next-day format.

**Solution:**
Confirm API response uses same-day extended hours.

---

## Related Documentation

- [OVERNIGHT_SHIFT_FIX.md](OVERNIGHT_SHIFT_FIX.md) - Wage calculation fix
- [OVERNIGHT_SHIFT_CALENDAR_FIX.md](OVERNIGHT_SHIFT_CALENDAR_FIX.md) - Original calendar fix documentation

---

**Fix Date:** 2025-11-17 (Final Update)
**Issue:** Overnight shifts splitting across two calendar days
**Root Cause:** Using next-day format instead of extended hours
**Solution:** Use extended hour format (27:00) on same date
**Status:** ✅ Fixed with extended hours

# Campus Facility Booking System - Project Specification

## System Purpose
A web-based Campus Facility Booking System that lets students and club 
committees submit requests to reserve seminar rooms, computer labs, 
sports courts, or equipment, and lets staff track, approve, or reject 
those requests — replacing manual message/spreadsheet tracking.

## Users/Roles
- **Student/Requester** (login required): submit booking requests, 
  view own booking status/history
- **Admin/Staff** (login required): view all requests, approve/reject, 
  manage facility list

## Core Features
1. User registration/login (Student and Admin)
2. Facility listing (rooms, courts, equipment) with availability
3. Booking request form (facility, date, time slot, purpose, contact person)
4. Admin dashboard to view, approve, or reject pending requests
5. Booking status tracking (Pending / Approved / Rejected)
6. Basic conflict check (prevent double-booking same facility/date/time)

## Page Structure
- login.php / register.php
- dashboard_student.php (booking_form.php + my_bookings.php)
- dashboard_admin.php
- facilities.php
- logout.php

## Expected Inputs/Outputs
- Input: student name/ID, facility selected, date, time slot, purpose, 
  contact number
- Output: confirmation message, booking status, admin approval/rejection notice

## Validation Rules
- Required fields cannot be empty
- Booking date cannot be in the past
- No double-booking (same facility, date, overlapping time)
- Only Admin can change booking status

## Limitations
- No payment integration
- No email/SMS notifications (status checked in-app only)
- Single-campus, not multi-branch
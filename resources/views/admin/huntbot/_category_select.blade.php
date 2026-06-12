<select name="category" required class="form-select rounded-3" {{ ($disabled ?? false) ? 'disabled' : '' }}>
    <option value="">-- Select category --</option>
    <optgroup label="Professional Services">
        <option value="accountant">Accountant / CPA</option>
        <option value="lawyer">Lawyer / Attorney</option>
        <option value="financial advisor">Financial Advisor</option>
        <option value="insurance agent">Insurance Agent</option>
        <option value="real estate agent">Real Estate Agent</option>
        <option value="mortgage broker">Mortgage Broker</option>
    </optgroup>
    <optgroup label="Healthcare">
        <option value="doctor">Doctor / Physician</option>
        <option value="dentist">Dentist</option>
        <option value="therapist">Therapist / Counselor</option>
        <option value="chiropractor">Chiropractor</option>
        <option value="optometrist">Optometrist</option>
        <option value="physical therapist">Physical Therapist</option>
    </optgroup>
    <optgroup label="Home Services">
        <option value="plumber">Plumber</option>
        <option value="electrician">Electrician</option>
        <option value="HVAC contractor">HVAC / AC Repair</option>
        <option value="house cleaner">House Cleaner</option>
        <option value="landscaper">Landscaper</option>
        <option value="handyman">Handyman</option>
        <option value="roofer">Roofer</option>
        <option value="painter">Painter</option>
        <option value="pest control">Pest Control</option>
    </optgroup>
    <optgroup label="Beauty & Personal Care">
        <option value="hair salon">Hair Salon</option>
        <option value="nail salon">Nail Salon</option>
        <option value="barber shop">Barber Shop</option>
        <option value="esthetician">Esthetician / Spa</option>
        <option value="massage therapist">Massage Therapist</option>
        <option value="makeup artist">Makeup Artist</option>
    </optgroup>
</select>

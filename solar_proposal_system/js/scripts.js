document.addEventListener('DOMContentLoaded', function() {
    // Material form handling
    const addMaterialBtn = document.getElementById('add-material-btn');
    if (addMaterialBtn) {
        addMaterialBtn.addEventListener('click', addMaterialRow);
    }

    // Set up event listeners for existing remove buttons
    setupRemoveMaterialButtons();

    // Calculate net landing cost
    const effectiveCostInput = document.getElementById('effective_cost');
    const subsidyAmountInput = document.getElementById('subsidy_amount');
    const netLandingCostInput = document.getElementById('net_landing_cost');

    if (effectiveCostInput && subsidyAmountInput) {
        effectiveCostInput.addEventListener('input', updateNetLandingCost);
        subsidyAmountInput.addEventListener('input', updateNetLandingCost);
    }

    // Toggle between existing and new customer
    const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
    const existingCustomerSection = document.getElementById('existing-customer-section');
    const newCustomerSection = document.getElementById('new-customer-section');

    customerTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'existing') {
                existingCustomerSection.style.display = 'block';
                newCustomerSection.style.display = 'none';
                document.getElementById('customer_name').removeAttribute('required');
                document.getElementById('existing_customer').setAttribute('required', 'required');
            } else {
                existingCustomerSection.style.display = 'none';
                newCustomerSection.style.display = 'block';
                document.getElementById('customer_name').setAttribute('required', 'required');
                document.getElementById('existing_customer').removeAttribute('required');
            }
        });
    });

    // Generate PDF button
    const generatePdfBtn = document.getElementById('generate-pdf');
    if (generatePdfBtn) {
        generatePdfBtn.addEventListener('click', function() {
            window.print();
        });
    }
});

// Function to add new material row
function addMaterialRow() {
    const materialsContainer = document.getElementById('materials-container');
    const materialRowCount = document.getElementById('material-row-count');
    const count = parseInt(materialRowCount.value) + 1;
    materialRowCount.value = count;

    const newRow = document.createElement('div');
    newRow.className = 'material-row';
    newRow.innerHTML = `
        <input type="text" name="material_description[]" required placeholder="Description">
        <input type="text" name="material_unit[]" required placeholder="Unit">
        <input type="number" name="material_quantity[]" required placeholder="Qty">
        <input type="text" name="material_size[]" placeholder="Size">
        <input type="text" name="material_manufacturer[]" placeholder="Manufacturer">
        <span class="remove-material" title="Remove"><i class="fas fa-trash"></i></span>
    `;

    materialsContainer.appendChild(newRow);
    setupRemoveMaterialButtons();
}

// Set up event listeners for material removal
function setupRemoveMaterialButtons() {
    const removeButtons = document.querySelectorAll('.remove-material');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.material-row').remove();
        });
    });
}

// Calculate net landing cost
function updateNetLandingCost() {
    const effectiveCost = parseFloat(document.getElementById('effective_cost').value) || 0;
    const subsidyAmount = parseFloat(document.getElementById('subsidy_amount').value) || 0;
    const netLandingCost = effectiveCost - subsidyAmount;
    
    document.getElementById('net_landing_cost').value = netLandingCost.toFixed(2);
}
// Initialize Email.js with your user ID
(function() {
    emailjs.init("fYfj0N417kppor5gl"); // Your public key
})();

/**
 * Send invoice email to customer
 * @param {string} customerEmail - Customer's email address
 * @param {string} customerName - Customer's name
 * @param {string} customerAddress - Customer's address
 * @param {string} customerPhone - Customer's phone number
 * @param {string} domainName - Domain name for SSL certificate
 * @param {string} invoiceNumber - Invoice number
 * @param {string} invoiceDate - Invoice date
 * @param {string} planName - SSL plan name
 * @param {string} planPrice - Formatted plan price
 * @param {string} total - Formatted total amount
 * @param {number} bankAmount - Raw bank amount for payment
 * @param {string} createdBy - Name of person who created the invoice
 */
function sendInvoiceEmail(customerEmail, customerName, customerAddress, customerPhone, domainName, invoiceNumber, invoiceDate, planName, planPrice, total, bankAmount, createdBy) {
    // Validate email address
    if (!customerEmail) {
        showErrorMessage("Invalid email address. Email could not be sent.");
        return;
    }
    
    if (!validateEmail(customerEmail)) {
        showErrorMessage("Invalid email format. Email could not be sent.");
        return;
    }
    
    // Prepare Email.js template parameters
    const templateParams = {
        to_email: customerEmail,
        email: customerEmail,
        customer_email: customerEmail,
        recipient: customerEmail,
        
        customer_name: customerName,
        customer_address: customerAddress,
        customer_phone: customerPhone,
        domain_name: domainName,
        invoice_number: invoiceNumber,
        invoice_date: invoiceDate,
        due_date: invoiceDate,
        plan_description: planName + ' for ' + domainName,
        plan_price: planPrice,
        subtotal: planPrice,
        total: total,
        bank_amount: bankAmount,
        created_by: createdBy
    };

    // Send the email using Email.js with your credentials
    emailjs.send('service_gbgi1rh', 'template_4wnqatt', templateParams)
        .then(function(response) {
            showSuccessMessage("Invoice has been emailed to " + customerEmail);
        })
        .catch(function(error) {
            showErrorMessage("We encountered an issue sending the invoice email. You can download it using the button below.");
        });
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} True if email is valid
 */
function validateEmail(email) {
    if (!email) return false;
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Show success message in the invoice modal
 * @param {string} message - Success message to display
 */
function showSuccessMessage(message) {
    const successAlert = document.createElement('div');
    successAlert.className = 'alert alert-success alert-dismissible fade show';
    successAlert.role = 'alert';
    successAlert.innerHTML = `
        <strong>Success!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add the alert to the invoice modal
    const modalBody = document.querySelector('#invoiceModal .modal-body');
    if (modalBody) {
        modalBody.prepend(successAlert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            try {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            } catch (error) {
                successAlert.remove();
            }
        }, 5000);
    }
}

/**
 * Show error message in the invoice modal
 * @param {string} message - Error message to display
 */
function showErrorMessage(message) {
    const errorAlert = document.createElement('div');
    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
    errorAlert.role = 'alert';
    errorAlert.innerHTML = `
        <strong>Error!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add the alert to the invoice modal
    const modalBody = document.querySelector('#invoiceModal .modal-body');
    if (modalBody) {
        modalBody.prepend(errorAlert);
    }
}
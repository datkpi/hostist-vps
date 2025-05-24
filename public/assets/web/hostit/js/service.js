// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Format numbers as currency - changed to USD
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    }

    // When a plan is selected
    const orderButtons = document.querySelectorAll('[data-bs-target="#domainModal"]');
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const price = this.getAttribute('data-price');
            const plan = this.getAttribute('data-plan');
            
            document.getElementById('selectedPlan').value = plan;
            document.getElementById('selectedPrice').value = price;
        });
    });

    // Generate random invoice number
    function generateInvoiceNumber() {
        return Math.floor(100000 + Math.random() * 900000);
    }

    // Format current date
    function formatDate() {
        const date = new Date();
        return date.toLocaleDateString('en-US');
    }

    // When proceeding to invoice
    document.getElementById('proceedToInvoice').addEventListener('click', function() {
        const domainForm = document.getElementById('domainForm');
        
        // Basic validation
        if (!domainForm.checkValidity()) {
            // Show validation messages
            domainForm.reportValidity();
            return;
        }
        
        try {
            // Get values from form
            const domainName = document.getElementById('domainName').value;
            const customerEmail = document.getElementById('customerEmail').value;
            const customerName = document.getElementById('customerName').value;
            const customerAddress = document.getElementById('customerAddress').value || '';
            const customerPhone = document.getElementById('customerPhone').value || '';
            const createdBy = document.getElementById('createdBy').value;
            const selectedPlan = document.getElementById('selectedPlan').value;
            const selectedPrice = parseFloat(document.getElementById('selectedPrice').value);
            
            // No VAT calculation - removed VAT
            const total = selectedPrice;
            
            // Update invoice details - using safe element access with optional chaining
            const invoiceNumber = generateInvoiceNumber();
            const currentDate = formatDate();
            
            // Safely set text content with helper function
            safeSetTextContent('invoiceNumber', invoiceNumber);
            safeSetTextContent('paymentCode', invoiceNumber);
            safeSetTextContent('invoiceDate', currentDate);
            safeSetTextContent('dueDate', currentDate);
            
            safeSetTextContent('customerNameDisplay', customerName);
            safeSetTextContent('customerEmailDisplay', customerEmail);
            safeSetTextContent('customerAddressDisplay', customerAddress);
            safeSetTextContent('customerPhoneDisplay', customerPhone);
            safeSetTextContent('domainDisplay', 'Domain: ' + domainName);
            safeSetTextContent('createdByDisplay', createdBy);
            
            safeSetTextContent('planDescription', selectedPlan + ' for ' + domainName);
            safeSetTextContent('planPrice', formatCurrency(selectedPrice));
            safeSetTextContent('subtotal', formatCurrency(selectedPrice));
            safeSetTextContent('total', formatCurrency(total));
            safeSetTextContent('paymentAmount', formatCurrency(total));
            safeSetTextContent('bankAmount', total);
            
            // Helper function to safely set text content
            function safeSetTextContent(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                } else {
                    console.warn(`Element with id '${id}' not found`);
                }
            }
            
            // Call email sending function (from email.js)
            if (typeof sendInvoiceEmail === 'function') {
                sendInvoiceEmail(
                    customerEmail,
                    customerName,
                    customerAddress,
                    customerPhone,
                    domainName,
                    invoiceNumber,
                    currentDate,
                    selectedPlan,
                    formatCurrency(selectedPrice),
                    formatCurrency(total),
                    total,
                    createdBy
                );
            }
            
            // Generate PDF for download
            generatePDFForDownload(invoiceNumber);
            
            // Close domain modal and show invoice modal
            closeModalSafely('domainModal');
            openModalSafely('invoiceModal');
            
            // Store invoice data in localStorage for status check page
            storeInvoiceData(invoiceNumber, currentDate, domainName, selectedPlan, total, customerEmail, customerName, customerPhone);
        } catch (error) {
            console.error("Error processing form:", error);
            alert("There was an error processing your request. Please try again.");
        }
    });
    
    // Helper function to safely close modal
    function closeModalSafely(modalId) {
        try {
            // Method 1: For Bootstrap 5.3
            const modalEl = document.getElementById(modalId);
            if (!modalEl) {
                console.warn(`Modal element with id '${modalId}' not found`);
                return;
            }
            
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
                return;
            }
            
            // Method 2: For jQuery Bootstrap
            if (typeof jQuery !== 'undefined') {
                jQuery('#' + modalId).modal('hide');
                return;
            }
            
            // Method 3: Fallback for Bootstrap 5
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.hide();
        } catch (error) {
            console.warn(`Error closing modal ${modalId}:`, error);
        }
    }
    
    // Helper function to safely open modal
    function openModalSafely(modalId) {
        try {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) {
                console.warn(`Modal element with id '${modalId}' not found`);
                return;
            }
            
            // Try Bootstrap 5 way
            try {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                return;
            } catch (error) {
                // Fallback for older Bootstrap versions
                if (typeof jQuery !== 'undefined') {
                    jQuery('#' + modalId).modal('show');
                }
            }
        } catch (error) {
            console.warn(`Error opening modal ${modalId}:`, error);
        }
    }
    
    // Function to store invoice data in localStorage
    function storeInvoiceData(invoiceNumber, invoiceDate, domain, plan, price, email, name, phone) {
        // Get existing invoices or create new array
        let invoices = [];
        try {
            const existingInvoices = localStorage.getItem('ssl_invoices');
            if (existingInvoices) {
                invoices = JSON.parse(existingInvoices);
            }
        } catch (e) {
            // If error parsing, start with empty array
            invoices = [];
        }
        
        // Create new invoice object
        const invoice = {
            invoiceNumber: invoiceNumber,
            invoiceDate: invoiceDate,
            dueDate: invoiceDate,
            domain: domain,
            plan: plan,
            price: price,
            email: email,
            name: name,
            phone: phone,
            status: 'Pending'
        };
        
        // Add to array (avoid duplicates)
        const existingIndex = invoices.findIndex(inv => inv.invoiceNumber === invoiceNumber);
        if (existingIndex >= 0) {
            invoices[existingIndex] = invoice;
        } else {
            invoices.push(invoice);
        }
        
        // Save back to localStorage
        localStorage.setItem('ssl_invoices', JSON.stringify(invoices));
    }
    
    // Function to generate PDF for download
    // Hàm tạo file PDF để tải xuống
function generatePDFForDownload(invoiceNumber) {
    // Sao chép nội dung từ modal invoice
    const invoiceModalContent = document.getElementById('invoiceModalContent');
    if (!invoiceModalContent) {
        console.warn("Không tìm thấy nội dung hóa đơn");
        return;
    }
    
    // Tạo bản sao để chỉnh sửa trước khi xuất PDF
    const invoiceElement = invoiceModalContent.cloneNode(true);
    
    // Loại bỏ header và footer của modal
    const modalHeader = invoiceElement.querySelector('.modal-header');
    const modalFooter = invoiceElement.querySelector('.modal-footer');
    if (modalHeader) modalHeader.remove();
    if (modalFooter) modalFooter.remove();
    
    // Đảm bảo hình ảnh QR code được hiển thị rõ ràng
    // Thay thế URL placeholder bằng URL thực tế nếu có
    const qrCodeImg = invoiceElement.querySelector('img[alt="QR Code"]');
    if (qrCodeImg) {
        // Đảm bảo hình ảnh hiện rõ và đúng kích thước
        qrCodeImg.style.display = "block";
        qrCodeImg.style.maxWidth = "150px";
        qrCodeImg.style.width = "150px";
        qrCodeImg.style.height = "150px";
        qrCodeImg.style.margin = "0 auto";
        
        // Đường dẫn tạm thời để test, nên thay bằng đường dẫn thực của bạn
        // qrCodeImg.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=HD" + invoiceNumber;
    }
    
    // Thêm element này vào document để render
    invoiceElement.style.width = '800px';
    invoiceElement.style.padding = '20px';
    invoiceElement.style.position = 'absolute';
    invoiceElement.style.left = '-9999px';
    document.body.appendChild(invoiceElement);
    
    // Đợi một chút để đảm bảo hình ảnh được tải
    setTimeout(() => {
        // Tạo PDF
        html2canvas(invoiceElement, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            imageTimeout: 3000, // Đợi lâu hơn cho hình ảnh
            onclone: function(clonedDoc) {
                // Đảm bảo QR code hiển thị trong bản sao
                const qrInClone = clonedDoc.querySelector('img[alt="QR Code"]');
                if (qrInClone) {
                    qrInClone.style.opacity = "1";
                    qrInClone.style.visibility = "visible";
                }
            }
        }).then(canvas => {
            // Xóa element tạm
            document.body.removeChild(invoiceElement);
            
            const imgData = canvas.toDataURL('image/png');
            
            try {
                const pdf = new jspdf.jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });
                
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                const imgX = (pdfWidth - imgWidth * ratio) / 2;
                const imgY = 30;
                
                // Thêm tiêu đề
                pdf.setFontSize(18);
                pdf.text('SSL Certificate Invoice #' + invoiceNumber, pdfWidth / 2, 20, { align: 'center' });
                
                // Thêm hình ảnh (nội dung hóa đơn)
                pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                
                // Thêm chân trang
                pdf.setFontSize(10);
                pdf.text('Thank you for your business with Hosttit!', pdfWidth / 2, pdfHeight - 10, { align: 'center' });
                
                // Tạo nút tải PDF
                const pdfBlob = pdf.output('blob');
                
                // Thêm nút tải xuống vào modal
                const downloadButton = document.createElement('a');
                downloadButton.href = URL.createObjectURL(pdfBlob);
                downloadButton.download = 'invoice_' + invoiceNumber + '.pdf';
                downloadButton.className = 'btn btn-info';
                downloadButton.textContent = 'Download Invoice PDF';
                downloadButton.style.marginRight = '10px';
                
                // Chèn nút tải xuống vào trước nút "Check Payment Status"
                const modalFooter = document.querySelector('#invoiceModal .modal-footer');
                if (modalFooter) {
                    // Kiểm tra xem nút đã tồn tại chưa
                    const existingButton = modalFooter.querySelector('.btn-info');
                    if (existingButton) {
                        existingButton.remove();
                    }
                    modalFooter.insertBefore(downloadButton, modalFooter.lastElementChild);
                }
            } catch (error) {
                console.error("Lỗi khi tạo PDF:", error);
                alert("Có lỗi khi tạo file PDF. Vui lòng thử lại.");
            }
        }).catch(error => {
            console.error("Lỗi khi tạo canvas:", error);
            alert("Có lỗi khi tạo file PDF. Vui lòng thử lại.");
            
            // Xóa element tạm trong trường hợp lỗi
            if (document.body.contains(invoiceElement)) {
                document.body.removeChild(invoiceElement);
            }
        });
    }, 500); // Đợi 500ms để đảm bảo hình ảnh được tải
}

    // Modify the Check Payment Status button to be a proper link
    const statusButton = document.querySelector('#invoiceModal .btn-primary');
    if (statusButton) {
        statusButton.addEventListener('click', function(e) {
            // No need to prevent default since we want to follow the link
            // e.preventDefault();
            
            // No need for alert anymore
            // alert('Payment status checking functionality would be implemented here.');
            
            // The href is already set in the HTML to "check-status.html"
        });
    }
});
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("category").addEventListener("change", function () {
        const categoryId = this.value;

        fetch("includes/load_services.php?category_id=" + categoryId)
            .then(response => response.json())
            .then(data => {
                let serviceDropdown = document.getElementById("service");
                serviceDropdown.innerHTML = '<option value="">-- Select Service --</option>';

                data.forEach(service => {
                    let option = document.createElement("option");
                    option.value = service.id;
                    option.textContent = service.name + " ($" + service.price + ")";
                    serviceDropdown.appendChild(option);
                });
            });
    });
});

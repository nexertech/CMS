<script>
window.initializeComplaintForm = function(root = document) {
    const categorySelect = root.querySelector('#category');
    const titleSelect = root.querySelector('#title');
    const titleOtherInput = root.querySelector('#title_other');
    const citySelect = root.querySelector('#city_id');
    const sectorSelect = root.querySelector('#sector_id');
    const houseSelect = root.querySelector('#house_id');
    const employeeSelect = root.querySelector('#assigned_employee_id');

    function handleTitleChange() {
        if (!titleSelect || !titleOtherInput) return;
        if (titleSelect.value === 'other') {
            titleOtherInput.style.display = 'block';
            titleOtherInput.required = true;
        } else {
            titleOtherInput.style.display = 'none';
            titleOtherInput.required = false;
        }
    }

    if (titleSelect) {
        titleSelect.addEventListener('change', handleTitleChange);
    }

    function filterEmployees() {
        if (!employeeSelect) return;
        const category = categorySelect ? categorySelect.value : '';
        const cityId = citySelect ? citySelect.value : '';
        const sectorId = sectorSelect ? sectorSelect.value : '';
        
        console.log('Filtering employees - Category:', category, 'City:', cityId, 'Sector:', sectorId);
        
        const currentSelectedId = employeeSelect.value;
        let currentlySelectedIsHidden = false;
        let visibleCount = 0;

        Array.from(employeeSelect.options).forEach(opt => {
            if (!opt.value) return; 
            const optCategory = opt.getAttribute('data-category') || '';
            const optCity = opt.getAttribute('data-city') || '';
            const optSector = opt.getAttribute('data-sector') || '';
            
            const matchCategory = !category || String(optCategory) === String(category);
            const matchCity = !cityId || String(optCity) === String(cityId);
            
            // STRICT sector matching: if sector is selected, employee MUST have that exact sector
            let matchSector = true;
            if (sectorId) {
                matchSector = String(optSector) === String(sectorId);
            }
            
            const show = matchCategory && matchCity && matchSector;
            
            if (show) visibleCount++;
            
            opt.hidden = !show;
            opt.style.display = show ? '' : 'none';
            opt.disabled = !show;

            if (!show && opt.value === currentSelectedId) {
                currentlySelectedIsHidden = true;
            }
        });

        console.log('Visible employees after filter:', visibleCount);

        if (currentlySelectedIsHidden) {
            employeeSelect.value = '';
            console.log('Current selection was hidden, cleared selection');
        }
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterEmployees();
            const category = this.value;
            if (!category) {
                if (titleSelect) titleSelect.innerHTML = '<option value="">Select Category First</option>';
                return;
            }

            if (titleSelect) {
                titleSelect.innerHTML = '<option value="">Loading titles...</option>';
                titleSelect.disabled = true;

                const url = `{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`;
                
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    titleSelect.innerHTML = '<option value="">Select Complaint Type</option>';
                    if (data && data.length > 0) {
                        data.sort((a, b) => (a.title || '').toLowerCase().localeCompare((b.title || '').toLowerCase(), undefined, { numeric: true }))
                            .forEach(title => {
                                const option = document.createElement('option');
                                option.value = title.id;
                                option.textContent = title.title;
                                titleSelect.appendChild(option);
                            });
                    }
                    
                    const otherOption = document.createElement('option');
                    otherOption.value = 'other';
                    otherOption.textContent = 'Other';
                    titleSelect.appendChild(otherOption);
                    
                    titleSelect.disabled = false;
                    
                    const previous = titleSelect.getAttribute('data-prev');
                    const custom = titleSelect.getAttribute('data-custom');
                    
                    if (previous) {
                        const opt = Array.from(titleSelect.options).find(o => o.value == previous);
                        if (opt) {
                            titleSelect.value = String(previous);
                            handleTitleChange();
                        }
                    } else if (custom && custom !== 'null' && custom !== '') {
                        titleSelect.value = 'other';
                        handleTitleChange();
                        if (titleOtherInput) titleOtherInput.value = custom;
                    }
                })
                .catch(error => {
                    console.error('Error loading titles:', error);
                    titleSelect.innerHTML = '<option value="">Failed to load titles</option>';
                    titleSelect.disabled = false;
                });
            }
        });

        if (categorySelect.value) {
            setTimeout(function() {
                categorySelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }

    // Store pending sector value for auto-population
    let pendingSectorValue = null;

    if (citySelect) {
        citySelect.addEventListener('change', function() {
            filterEmployees();
            const cityId = this.value;
            if (!cityId) {
                if (sectorSelect) {
                    sectorSelect.innerHTML = '<option value="">Select GE Groups First</option>';
                    sectorSelect.disabled = true;
                }
                return;
            }

            if (sectorSelect) {
                sectorSelect.innerHTML = '<option value="">Loading GE Nodes...</option>';
                sectorSelect.disabled = true;

                fetch(`{{ route('admin.sectors.by-city') }}?city_id=${cityId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    const prevVal = sectorSelect.value;
                    sectorSelect.innerHTML = '<option value="">Select GE Nodes</option>';
                    data.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        sectorSelect.appendChild(option);
                    });
                    sectorSelect.disabled = false;
                    
                    // Apply pending sector value if set, otherwise preserve previous value
                    if (pendingSectorValue) {
                        sectorSelect.value = pendingSectorValue;
                        pendingSectorValue = null; // Clear after use
                        sectorSelect.dispatchEvent(new Event('change'));
                    } else if (prevVal) {
                        sectorSelect.value = prevVal;
                    }
                    
                    filterEmployees();
                });
            }
        });
    }

    if (sectorSelect) {
        sectorSelect.addEventListener('change', function() {
            filterEmployees();
        });
    }

    if (houseSelect) {
        houseSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                const nameInput = root.querySelector('#complainant_name');
                const phoneInput = root.querySelector('#client_phone');
                if (nameInput) nameInput.value = option.getAttribute('data-name') || '';
                if (phoneInput) phoneInput.value = option.getAttribute('data-phone') || '';
                
                // Auto-populate city and sector from house data
                const houseCity = option.getAttribute('data-city');
                const houseSector = option.getAttribute('data-sector');
                
                if (houseCity && citySelect) {
                    // Store the sector value to be applied after AJAX loads sectors
                    if (houseSector) {
                        pendingSectorValue = houseSector;
                    }
                    
                    citySelect.value = houseCity;
                    // Trigger change event to load sectors
                    citySelect.dispatchEvent(new Event('change'));
                }
            }
        });
        
        // Trigger on page load if house is already selected (for edit mode)
        if (houseSelect.value) {
            houseSelect.dispatchEvent(new Event('change'));
        }
    }

    filterEmployees();
    feather.replace();
    
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(root).find('.select2').each(function() {
            const $this = $(this);
            const parent = $this.closest('.modal').length ? $this.closest('.modal') : null;
            $this.select2({
                dropdownParent: parent
            });
        });
    }
};
</script>

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
        const titleDropdownContainer = root.querySelector('#titleDropdownContainer');
        const titleInputContainer = root.querySelector('#titleInputContainer');

        if (titleSelect.value === 'other') {
            if (titleDropdownContainer) titleDropdownContainer.style.display = 'none';
            if (titleInputContainer) titleInputContainer.style.display = 'block';
            titleOtherInput.style.display = 'block';
            titleOtherInput.required = true;
            titleSelect.removeAttribute('required');
            setTimeout(() => titleOtherInput.focus(), 100);
            if (typeof feather !== 'undefined') feather.replace();
        } else {
            if (titleDropdownContainer) titleDropdownContainer.style.display = 'block';
            if (titleInputContainer) titleInputContainer.style.display = 'none';
            titleOtherInput.style.display = 'none';
            titleOtherInput.required = false;
            titleSelect.required = true;
        }
    }

    if (titleSelect) {
        titleSelect.addEventListener('change', handleTitleChange);
    }

    const btnBackToSelect = root.querySelector('#btn_back_to_select');
    if (btnBackToSelect) {
        btnBackToSelect.addEventListener('click', function() {
            if (titleSelect) {
                titleSelect.value = '';
                handleTitleChange();
            }
            if (titleOtherInput) {
                titleOtherInput.value = '';
            }
        });
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
            const optSectorRaw = opt.getAttribute('data-sector') || opt.getAttribute('data-sectors') || '';
            const optSectors = optSectorRaw ? optSectorRaw.split(',').map(s => s.trim()) : [];
            
            const matchCategory = !category || String(optCategory) === String(category);
            let matchSector = true;
            if (sectorId) {
                matchSector = optSectors.length === 0 || optSectors.includes(String(sectorId));
            }
            const matchCity = !cityId || String(optCity) === String(cityId) || (sectorId && optSectors.includes(String(sectorId)));
            
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

    function loadSubCategories(category, selectedSubCategoryId = null) {
        const subCategorySelect = root.querySelector('#sub_category_id');
        if (!subCategorySelect) return;

        if (!category) {
            subCategorySelect.innerHTML = '<option value="">Select Category First</option>';
            return;
        }

        subCategorySelect.innerHTML = '<option value="">Loading...</option>';

        const url = `{{ route('admin.sub-categories.by-category') }}?category=${encodeURIComponent(category)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            subCategorySelect.innerHTML = '<option value="">Select Sub Category (Optional)</option>';
            const list = (data && data.sub_categories) ? data.sub_categories : (Array.isArray(data) ? data : []);
            const targetVal = selectedSubCategoryId || subCategorySelect.getAttribute('data-old-value');
            if (list.length > 0) {
                list.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    if (targetVal && String(sub.id) === String(targetVal)) {
                        option.selected = true;
                    }
                    subCategorySelect.appendChild(option);
                });
            }
        })
        .catch(err => {
            console.error('Error loading sub categories:', err);
            subCategorySelect.innerHTML = '<option value="">Select Sub Category (Optional)</option>';
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterEmployees();
            const category = this.value;
            loadSubCategories(category);
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
                    
                    const hasValidPrevious = previous && previous !== '0' && previous !== 'null' && previous !== 'undefined' && previous !== 'other';
                    
                    if (hasValidPrevious) {
                        const opt = Array.from(titleSelect.options).find(o => o.value == previous);
                        if (opt) {
                            titleSelect.value = String(previous);
                            handleTitleChange();
                        } else if (custom && custom !== 'null' && custom !== '' && custom !== 'undefined') {
                            titleSelect.value = 'other';
                            handleTitleChange();
                            if (titleOtherInput) titleOtherInput.value = custom;
                        }
                    } else if (custom && custom !== 'null' && custom !== '' && custom !== 'undefined') {
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
        // For Select2 AJAX: read house data from the select2:select event
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(houseSelect).on('select2:select', function(e) {
                const data = e.params.data;
                if (data) {
                    const nameInput = root.querySelector('#complainant_name');
                    const phoneInput = root.querySelector('#client_phone') || root.querySelector('#phone');
                    const addrInput = root.querySelector('#address');
                    if (nameInput) nameInput.value = data.name || '';
                    if (phoneInput) phoneInput.value = data.phone || '';
                    if (addrInput) addrInput.value = data.address || '';

                    // Auto-populate city and sector from house data
                    if (data.city_id && citySelect) {
                        if (data.sector_id) {
                            pendingSectorValue = data.sector_id;
                        }
                        citySelect.value = data.city_id;
                        citySelect.dispatchEvent(new Event('change'));
                    }
                }
                filterEmployees();
            });

            $(houseSelect).on('select2:clear', function() {
                const nameInput = root.querySelector('#complainant_name');
                const phoneInput = root.querySelector('#client_phone') || root.querySelector('#phone');
                const addrInput = root.querySelector('#address');
                if (nameInput) nameInput.value = '';
                if (phoneInput) phoneInput.value = '';
                if (addrInput) addrInput.value = '';
            });
        } else {
            // Fallback for non-Select2 (standard <option> data-* attributes)
            houseSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                if (option.value) {
                    const nameInput = root.querySelector('#complainant_name');
                    const phoneInput = root.querySelector('#client_phone');
                    if (nameInput) nameInput.value = option.getAttribute('data-name') || '';
                    if (phoneInput) phoneInput.value = option.getAttribute('data-phone') || '';

                    const houseCity = option.getAttribute('data-city');
                    const houseSector = option.getAttribute('data-sector');

                    if (houseCity && citySelect) {
                        if (houseSector) {
                            pendingSectorValue = houseSector;
                        }
                        citySelect.value = houseCity;
                        citySelect.dispatchEvent(new Event('change'));
                    }
                }
            });
        }
        
        // Trigger on page load if house is already selected (for edit mode)
        if (houseSelect.value) {
            houseSelect.dispatchEvent(new Event('change'));
        }
    }

    filterEmployees();
    feather.replace();
    
    if (typeof $ !== 'undefined' && $.fn.select2) {
        const $houseSelect = $(root).find('#house_id');
        if ($houseSelect.length) {
            $houseSelect.select2({
                placeholder: "Type House No. or Search...",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: "{{ route('admin.houses.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            city_id: $(root).find('#city_id').val(),
                            sector_id: $(root).find('#sector_id').val()
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        }

        $(root).find('.select2:not(#house_id)').each(function() {
            const $this = $(this);
            const parent = $this.closest('.modal').length ? $this.closest('.modal') : null;
            $this.select2({
                dropdownParent: parent
            });
        });
    }
};</script>

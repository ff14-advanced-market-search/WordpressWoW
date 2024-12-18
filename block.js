const {
    registerBlockType
} = wp.blocks;
const {
    createElement,
    Fragment
} = wp.element;
const {
    InspectorControls
} = wp.blockEditor;
const {
    TextControl,
    ToggleControl,
    SelectControl
} = wp.components;

// Function to only allow numbers and commas in the Item IDs field
const sanitizeItemIds = (value) => {
    // Remove all non-numeric and non-comma characters
    return value.replace(/[^0-9,]/g, '');
};

// Register the block
registerBlockType('custom/api-block', {
    title: 'Custom API Block Gold Capped',
    icon: 'admin-network',
    category: 'widgets',
    attributes: {
        item_ids: {
            type: 'string',
            default: ''
        },
        pets: {
            type: 'boolean',
            default: false
        },
        game_edition: {
            type: 'string',
            default: ''
        },
    },
    edit: function(props) {
        const {
            attributes,
            setAttributes
        } = props;
        const {
            item_ids,
            pets,
            game_edition
        } = attributes;

        return createElement(
            Fragment,
            null,
            createElement(
                InspectorControls,
                null,
                createElement(TextControl, {
                    label: 'Item IDs (comma separated)',
                    value: item_ids,
                    onChange: function(value) {
                        // Sanitize the input by removing any invalid characters
                        setAttributes({
                            item_ids: sanitizeItemIds(value)
                        });
                    },
                }),
                createElement(ToggleControl, {
                    label: 'Pets',
                    checked: pets,
                    onChange: function(value) {
                        setAttributes({
                            pets: value
                        });
                    },
                }),
                createElement(SelectControl, {
                    label: 'Game Edition',
                    value: game_edition,
                    options: [
                        { label: 'Select Game Edition', value: '' },
                        { label: 'Retail', value: 'retail' },
                        { label: 'Classic', value: 'classic' },
                        { label: 'Sod', value: 'sod' },
                        { label: 'Anniversary', value: 'anniversary' },
                        { label: 'Wrath', value: 'wrath' }
                    ],
                    onChange: function(value) {
                        setAttributes({
                            game_edition: value
                        });
                    },
                })
            ),
            createElement('div', null, 'Custom API Block Gold Capped')
        );
    },
    save: function() {
        return null; // Dynamic blocks do not save content
    },
});

   document.addEventListener("DOMContentLoaded", function () {
        // Function to update styles or add custom classes
        function updateTooltipStyles() {
            // Define your custom class logic
            const qualityMap = {
                q0: "poor-item",      // Custom class for Poor
                q1: "common-item",    // Custom class for Common
                q2: "uncommon-item",  // Custom class for Uncommon
                q3: "rare-item",      // Custom class for Rare
                q4: "epic-item",      // Custom class for Epic
                q5: "legendary-item"  // Custom class for Legendary
            };

            // Find all links with rarity classes
            const wowheadLinks = document.querySelectorAll("a[class^='q']");

            wowheadLinks.forEach(link => {
                // Find the class that starts with "q"
                const rarityClass = Array.from(link.classList).find(cls => cls.startsWith("q"));
                
                if (rarityClass && qualityMap[rarityClass]) {
                    // Remove existing rarity classes (optional)
                    link.classList.remove(...Object.keys(qualityMap));
                    // Add custom class based on rarity
                    link.classList.add(qualityMap[rarityClass]);
                }
            });
        }

        // Run after Wowhead script processes links
        setTimeout(updateTooltipStyles, 1000); // Delayed to ensure tooltips are applied
    });

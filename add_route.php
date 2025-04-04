<?php
session_start();
require_once 'config.php';
function fetchVehicles() {
    fetch('get_vehicles.php')
        .then(response ) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(vehicles => {
            const vehicleSelect = document.getElementById('vehicle');
            
            // Clear existing options except the first one
            while (vehicleSelect.options.length > 1) {
                vehicleSelect.remove(1);
            }
            
            // Add new options
            vehicles.forEach(vehicle => {
                const option = new Option(
                    `${vehicle.registration} (${vehicle.seat_capacity} seats)`, 
                    vehicle.vehicle_id
                );
                vehicleSelect.add(option);
            });
            
            // Enable the select element if it was disabled
            vehicleSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching vehicles:', error);
            alert('Failed to load vehicles. Please try again later.');
        });
}
?>
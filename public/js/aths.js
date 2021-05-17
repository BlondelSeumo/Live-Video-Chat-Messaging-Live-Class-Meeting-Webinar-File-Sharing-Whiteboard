let deferredPrompt
const athsAlert = document.querySelector('.aths-alert-backdrop')
const athsAlertBtn = document.querySelector('.aths-alert-action-btn')
const athsAlertCancelBtn = document.querySelector('.aths-alert-action-cancel')
athsAlert.style.display = 'none'

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault()
    // Stash the event so it can be triggered later.
    deferredPrompt = e

    let athsAlertShown = 0
    const record = JSON.parse(window.localStorage.getItem('KMAthsAlertShown'))
    if(record && new Date().getTime() < record.timestamp) {
        athsAlertShown = record.value
    }

    if(!athsAlertShown) {
        // Update UI to notify the user they can add to home screen
        athsAlert.style.display = 'flex'

        athsAlertBtn.addEventListener('click', (e) => {
            // hide our user interface that shows our A2HS button
            athsAlert.style.display = 'none'
            // Show the prompt
            deferredPrompt.prompt()
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    window.localStorage.setItem('KMAthsAlertShown', JSON.stringify({ value: 1, timestamp: new Date().getTime() + 1440 * 60 * 1000 }))
                    console.log('User accepted the A2HS prompt')
                } else {
                    window.localStorage.setItem('KMAthsAlertShown', JSON.stringify({ value: 1, timestamp: new Date().getTime() + 1440 * 60 * 1000 }))
                    console.log('User dismissed the A2HS prompt')
                }
                deferredPrompt = null
            })
        })

        athsAlertCancelBtn.addEventListener('click', (e) => {
            // hide our user interface that shows our A2HS button
            athsAlert.style.display = 'none'
            window.localStorage.setItem('KMAthsAlertShown', JSON.stringify({ value: 1, timestamp: new Date().getTime() + 1440 * 60 * 1000 }))
        })
    }
})

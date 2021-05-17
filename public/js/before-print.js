window.addEventListener("beforeprint", function(){
    let printStyleSheet = document.createElement("style");
    printStyleSheet.classList.add("print-stylesheet");
    document.head.appendChild(printStyleSheet)

    let printRulesText = "";
    Array.from(document.styleSheets).forEach(styleSheet => {
        Array.from(styleSheet.cssRules)
        .filter(rule => rule.media && /(min-width: 768px)/g.test(rule.media.mediaText))
        .forEach(rule => {
            Array.from(rule.cssRules).forEach(rule => printRulesText += rule.cssText)
        })
    })
    printStyleSheet.innerHTML = printRulesText;
})
window.addEventListener("afterprint", function(){
    Array.from(document.querySelectorAll(".print-stylesheet")).forEach(style => style.remove())
})
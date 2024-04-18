class student_answer extends HTMLElement {
	// component implementation goes here
	render() {
		this.innerHTML = `
        <div style="text-align: center; font-family: sans-serif">
            <h1>${this.heading}</h1>
            <p>${this.subheading}</p>
        </div>
      `;
	}
}

customElements.define("student-answer", student_answer);

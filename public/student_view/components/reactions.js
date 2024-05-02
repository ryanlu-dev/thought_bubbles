class Reactions extends HTMLElement {
	constructor() {
		super();
	}

	render() {
		this.innerHTML = `
		<form method="POST" action="post_reply.php" id="post-reply-form">
			<button type = 'submit'><img src = '../../resources/reactions/heart.svg' alt = 'heart'/></button>
		</form>
		`;
	}
}

customElements.define("student-reaction", Reactions);

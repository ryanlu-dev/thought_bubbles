<div class="container">
			<h3>Debugging:</h3>
			<form method="POST" action="../app/post_interaction.php">
				<div class="form-group">
					<label for="parentID">Message, reply, or react to: </label>
					<input
						class="form-control"
						type="number"
						name="parent_id"
						id="parentID"
						placeholder="InteractionID"
					/>
				</div>
				<div class="form-group">
					<label for="sessionID">Session ID: </label>
					<input
						class="form-control"
						type="number"
						name="session_id"
						id="sessionID"
						placeholder="SessionID"
					/>
				</div>
				<div class="form-group">
					<label for="studentID">Student ID: </label>
					<input
						class="form-control"
						type="number"
						name="student_id"
						id="studentID"
						placeholder="StudentID"
					/>
				</div>
				<div class="form-group">
					<label for="interactionType"
						>Interaction Type (Choose prompt, message, reply, reaction):
					</label>
					<input
						class="form-control"
						type="text"
						name="interaction_type"
						id="interactionType"
						placeholder="InteractionType"
					/>
				</div>
				<div class="form-group">
					<label for="content">Content: </label>
					<input
						class="form-control"
						type="text"
						name="content"
						id="content"
						placeholder="Content"
					/>
				</div>

				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
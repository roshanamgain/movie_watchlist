<?php
class MovieValidator {
    private $errors = [];
    
    /**
     * Validate movie data before insert/update
     */
    public function validate($movieData) {
        $this->errors = [];
        
        // Validate Title
        $this->validateTitle($movieData['Title'] ?? '');
        
        // Validate Genre
        $this->validateGenre($movieData['Genre'] ?? '');
        
        // Validate Release Year
        $this->validateReleaseYear($movieData['ReleaseYear'] ?? null);
        
        // Validate Poster URL
        $this->validatePosterURL($movieData['PosterURL'] ?? '');
        
        // Validate Rating
        $this->validateRating($movieData['TMDBRating'] ?? null);
        
        // Validate Description
        $this->validateDescription($movieData['Description'] ?? '');
        
        return empty($this->errors);
    }
    
    private function validateTitle($title) {
        if (empty($title)) {
            $this->errors['Title'] = 'Movie title is required.';
        } elseif (strlen($title) < 2) {
            $this->errors['Title'] = 'Movie title must be at least 2 characters long.';
        } elseif (strlen($title) > 255) {
            $this->errors['Title'] = 'Movie title cannot exceed 255 characters.';
        } elseif (preg_match('/[<>\"\'\%]/', $title)) {
            $this->errors['Title'] = 'Movie title contains invalid characters.';
        }
    }
    
    private function validateGenre($genre) {
        $allowedGenres = ['Action', 'Adventure', 'Animation', 'Biography', 'Comedy', 
                          'Crime', 'Drama', 'Fantasy', 'Horror', 'Romance', 
                          'Sci-Fi', 'Thriller', 'Western'];
        
        if (empty($genre)) {
            $this->errors['Genre'] = 'Genre is required.';
        } elseif (!in_array($genre, $allowedGenres)) {
            $this->errors['Genre'] = 'Invalid genre selected. Please choose from the list.';
        }
    }
    
    private function validateReleaseYear($year) {
        $currentYear = date('Y');
        $minYear = 1888; // First movie ever made
        
        if (empty($year)) {
            $this->errors['ReleaseYear'] = 'Release year is required.';
        } elseif (!is_numeric($year)) {
            $this->errors['ReleaseYear'] = 'Release year must be a number.';
        } elseif ($year < $minYear) {
            $this->errors['ReleaseYear'] = "Release year cannot be earlier than $minYear.";
        } elseif ($year > $currentYear + 5) {
            $this->errors['ReleaseYear'] = "Release year cannot be more than 5 years in the future.";
        } elseif (!checkdate(1, 1, $year)) {
            $this->errors['ReleaseYear'] = 'Invalid year format.';
        }
    }
    
    private function validatePosterURL($url) {
        if (empty($url)) {
            $this->errors['PosterURL'] = 'Poster URL is required.';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors['PosterURL'] = 'Please enter a valid URL (e.g., https://example.com/poster.jpg).';
        } elseif (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            // Optional: Check for image extensions
            $this->errors['PosterURL'] = 'Poster URL should point to an image file (jpg, jpeg, png, gif, webp).';
        }
    }
    
    private function validateRating($rating) {
        if ($rating === null || $rating === '') {
            $this->errors['TMDBRating'] = 'Rating is required.';
        } elseif (!is_numeric($rating)) {
            $this->errors['TMDBRating'] = 'Rating must be a number.';
        } elseif ($rating < 0) {
            $this->errors['TMDBRating'] = 'Rating cannot be negative.';
        } elseif ($rating > 10) {
            $this->errors['TMDBRating'] = 'Rating cannot exceed 10.';
        }
    }
    
    private function validateDescription($description) {
        if (empty($description)) {
            $this->errors['Description'] = 'Description is required.';
        } elseif (strlen($description) < 20) {
            $this->errors['Description'] = 'Description must be at least 20 characters long.';
        } elseif (strlen($description) > 2000) {
            $this->errors['Description'] = 'Description cannot exceed 2000 characters.';
        }
    }
    
    /**
     * Check if a movie with the same title already exists
     */
    public function isDuplicateTitle($pdo, $title, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM tblmovie WHERE Title = :title";
        $params = [':title' => $title];
        
        if ($excludeId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Sanitize movie data
     */
    public function sanitize($movieData) {
        $sanitized = [];
        
        // Sanitize Title
        $sanitized['Title'] = htmlspecialchars(trim($movieData['Title'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Sanitize Genre
        $sanitized['Genre'] = htmlspecialchars(trim($movieData['Genre'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Sanitize Release Year
        $sanitized['ReleaseYear'] = filter_var($movieData['ReleaseYear'] ?? 0, FILTER_VALIDATE_INT);
        
        // Sanitize URL
        $sanitized['PosterURL'] = filter_var(trim($movieData['PosterURL'] ?? ''), FILTER_SANITIZE_URL);
        
        // Sanitize Rating
        $sanitized['TMDBRating'] = filter_var($movieData['TMDBRating'] ?? 0, FILTER_VALIDATE_FLOAT);
        
        // Sanitize Description
        $sanitized['Description'] = htmlspecialchars(trim($movieData['Description'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get formatted error messages
     */
    public function getErrorMessages() {
        $messages = [];
        foreach ($this->errors as $field => $error) {
            $messages[] = "$field: $error";
        }
        return $messages;
    }
}
?>
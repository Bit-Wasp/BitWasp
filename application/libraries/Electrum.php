<?php

require_once(dirname(__FILE__).'/ecc-lib/auto_load.php');
require_once(dirname(__FILE__).'/BitcoinLib.php');

/**
 * Electrum Library
 * 
 * This class contains functions which implement the electrum standard
 * functionality.
 * 
 * - A function which stretches the seed many times into a 64bit key.
 * - A function to generate a master public key from the seed.
 * - A function to generate a private key given the seed, child number, 
 *   and whether it's a change address. 
 * - A function to generate the public key from the master public key.
 * - A function to generate an address from the master public key and an
 *   address version.
 * - A function to decode a seed from a sequence of words from the electrum
 *   word list.
*/

class Electrum {

	/**
	 * Stretch Seed
	 * 
	 * This function accepts the wallets $seed as input, and stretches it by
	 * hashing it many times. It returns the result as a hexadecimal string.
	 * 
	 * @param	string	$seed
	 * @return	string
	 */
	public function stretch_seed($seed) {
		$oldseed = $seed;
		
		// Perform sha256 hash 5 times per iteration
		for($i = 0; $i < 20000; $i++) {
			// Hash should return binary data
			$seed = hash('sha256', hash('sha256', hash('sha256', hash('sha256', hash('sha256', $seed.$oldseed, TRUE).$oldseed, TRUE).$oldseed, TRUE).$oldseed, TRUE).$oldseed, TRUE);
		}

		// Convert binary data to hex.
		$seed = bin2hex($seed);
		return array('original' => $oldseed,
					 'seed' => $seed);
	}
	
	/**
	 * Generate MPK
	 * 
	 * This function accepts a seed, or secret exponent, and returns the
	 * master public key, from which new public keys and addresses are 
	 * derived.
	 * 
	 * @param	string	$seed
	 * @return	string
	 */
	public function generate_mpk($seed) {
		if(strlen($seed) == 32) {
			$seed = self::stretch_seed($seed);
			$seed = $seed['seed'];
		}
		// Multiply the seed by generator point.
		$g = SECcurve::generator_secp256k1();
		$seed = gmp_init($seed, 16);
		$secretG = Point::mul($seed, $g);
		$x =  str_pad(gmp_strval($secretG->getX(), 16), 64, '0', STR_PAD_LEFT);
		$y =  str_pad(gmp_strval($secretG->getY(), 16), 64, '0', STR_PAD_LEFT);
		
		// Return the master public key.
		return $x.$y;
	}

	/**
	 * Generate Private Key
	 * 
	 * This function accepts a string or a secret exponent as a $seed, 
	 * and generates a private key for the $i'th address. Setting the 
	 * change parameter to 1 will create different addresses, which the
	 * client uses for change. 
	 * 
	 * @param	string	$seed
	 * @param	int	$iteration
	 * @param	int	$change(optional)
	 */
	public function generate_private_key($seed, $iteration, $change = 0) {
		$change = ($change == 0) ? '0' : '1';
		if(strlen($seed) == 32) {
			$seed = self::stretch_seed($seed);
			$seed = $seed['seed'];
		}

		$mpk = self::generate_mpk($seed);

		$g = SECcurve::generator_secp256k1();
		$n = $g->getOrder();
		// Generate the private key by calculating: 
		// ($seed + (sha256(sha256($iteration:$change:$binary_mpk))) % $n)h
		$private_key = gmp_strval(
			gmp_Utils::gmp_mod2(
				gmp_add(
					gmp_init($seed, 16),
					gmp_init(hash('sha256', hash('sha256', "$iteration:$change:".pack('H*', $mpk), TRUE)), 16)
				),
				$n
			),
			16
		);
		
		return $private_key;
	}
	
	/**
	 * Public Key From MPK
	 * 
	 * This function is used to generate a public key from the supplied
	 * $mpk - the master public key, and an $iteration indicating which 
	 * address in the sequence should be generated.
	 * 
	 * @param	string	$mpk
	 * @param	int	$iteration
	 * @return	string
	 */
	public function public_key_from_mpk($mpk, $iteration, $change = 0, $compressed = FALSE) {
		$change = ($change == 0) ? '0' : '1';
		
		// Generate the curve, and the generator point.
		$curve = SECcurve::curve_secp256k1();
		$gen = SECcurve::generator_secp256k1();
			
		// Prepare the input values, by converting the MPK to X and Y coordinates
		$x = gmp_init(substr($mpk, 0, 64), 16);
		$y = gmp_init(substr($mpk, 64, 64), 16);
		
		// Generate a scalar from the $iteration and $mpk
		$z = gmp_init(hash('sha256', hash('sha256', "$iteration:$change:" . pack('H*', $mpk), TRUE)), 16);

		// Add the Point defined by $x and $y, to the result of EC multiplication of $z by $gen
		$pt = Point::add(new Point($curve, $x, $y), Point::mul($z, $gen));
		
		// Generate the uncompressed public key.
		$keystr = '04'
				. str_pad(gmp_strval($pt->x, 16), 64, '0', STR_PAD_LEFT)
				. str_pad(gmp_strval($pt->y, 16), 64, '0', STR_PAD_LEFT);
				
		return ($compressed == TRUE) ? BitcoinLib::compress_public_key($keystr) : $keystr;
	}

	/**
	 * Address from MPK
	 * 
	 * Generate an address from the $mpk, and $iteration. This function 
	 * uses the public_key_from_mpk() function, and converts the result
	 * to the bitcoin address.
	 * 
	 * @param	string	$mpk
	 * @param	int	$iteration
	 * @return	string
	 */
	public function address_from_mpk($mpk, $iteration, $magic_byte, $change = 0, $compressed = FALSE) {
		$change = ($change == 0) ? 0 : 1;
		$public_key = self::public_key_from_mpk($mpk, $iteration, $change, $compressed);
		$address = BitcoinLib::public_key_to_address($public_key, $magic_byte);
		return $address;
	}

	/**
	 * Decode Mnemonic
	 * 
	 * This function decodes a string of 12 words to convert to the electrum
	 * seed. This is an implementation of http://tools.ietf.org/html/rfc1751,
	 * which is how electrum generates a 128-bit key from 12 words. 
	 * 
	 * @param	string	$words
	 * @return	string
	 */
	public function decode_mnemonic($words) {		
		$words = explode(" ", $words);
		$out = '';
		$n = 1626;
		for($i = 0; $i < (count($words)/3); $i++) { 
			$a = (3*$i);
			list($word1, $word2, $word3) = array($words[$a], $words[$a+1], $words[$a+2]);
			
			$index_w1 = array_search($word1, self::$words); 
			$index_w2 = array_search($word2, self::$words)%$n; 
			$index_w3 = array_search($word3, self::$words)%$n;
			$x = $index_w1+$n*(gmp_Utils::gmp_mod2($index_w2-$index_w1,$n))+$n*$n*(gmp_Utils::gmp_mod2($index_w3-$index_w2,$n));
			$out .= BitcoinLib::hex_encode($x);
		}
		return $out;
	}
	
	/**
	 * Words
	 * 
	 * These words are used in electrum to generate 128 bit keys from 
	 * 12 word combinations, in accordance with http://tools.ietf.org/html/rfc1751
	 */
	public static $words = array("like", "just", "love", "know", "never", "want", "time", 
"out", "there", "make", "look", "eye", "down", "only", "think", 
"heart", "back", "then", "into", "about", "more", "away", "still", 
"them", "take", "thing", "even", "through", "long", "always", 
"world", "too", "friend", "tell", "try", "hand", "thought", "over", 
"here", "other", "need", "smile", "again", "much", "cry", "been", 
"night", "ever", "little", "said", "end", "some", "those", "around", 
"mind", "people", "girl", "leave", "dream", "left", "turn", "myself",
"give", "nothing", "really", "off", "before", "something", "find", 
"walk", "wish", "good", "once", "place", "ask", "stop", "keep", 
"watch", "seem", "everything", "wait", "got", "yet", "made", 
"remember", "start", "alone", "run", "hope", "maybe", "believe", 
"body", "hate", "after", "close", "talk", "stand", "own", "each", 
"hurt", "help", "home", "god", "soul", "new", "many", "two", 
"inside", "should", "true", "first", "fear", "mean", "better", 
"play", "another", "gone", "change", "use", "wonder", "someone", 
"hair", "cold", "open", "best", "any", "behind", "happen", "water", 
"dark", "laugh", "stay", "forever", "name", "work", "show", "sky", 
"break", "came", "deep", "door", "put", "black", "together", "upon", 
"happy", "such", "great", "white", "matter", "fill", "past", 
"please", "burn", "cause", "enough", "touch", "moment", "soon", 
"voice", "scream", "anything", "stare", "sound", "red", "everyone", 
"hide", "kiss", "truth", "death", "beautiful", "mine", "blood", 
"broken", "very", "pass", "next", "forget", "tree", "wrong", "air", 
"mother", "understand", "lip", "hit", "wall", "memory", "sleep", 
"free", "high", "realize", "school", "might", "skin", "sweet", 
"perfect", "blue", "kill", "breath", "dance", "against", "fly", 
"between", "grow", "strong", "under", "listen", "bring", "sometimes",
"speak", "pull", "person", "become", "family", "begin", "ground", 
"real", "small", "father", "sure", "feet", "rest", "young", 
"finally", "land", "across", "today", "different", "guy", "line", 
"fire", "reason", "reach", "second", "slowly", "write", "eat", 
"smell", "mouth", "step", "learn", "three", "floor", "promise", 
"breathe", "darkness", "push", "earth", "guess", "save", "song", 
"above", "along", "both", "color", "house", "almost", "sorry", 
"anymore", "brother", "okay", "dear", "game", "fade", "already", 
"apart", "warm", "beauty", "heard", "notice", "question", "shine", 
"began", "piece", "whole", "shadow", "secret", "street", "within", 
"finger", "point", "morning", "whisper", "child", "moon", "green", 
"story", "glass", "kid", "silence", "since", "soft", "yourself", 
"empty", "shall", "angel", "answer", "baby", "bright", "dad", "path",
"worry", "hour", "drop", "follow", "power", "war", "half", "flow", 
"heaven", "act", "chance", "fact", "least", "tired", "children", 
"near", "quite", "afraid", "rise", "sea", "taste", "window", "cover",
"nice", "trust", "lot", "sad", "cool", "force", "peace", "return", 
"blind", "easy", "ready", "roll", "rose", "drive", "held", "music", 
"beneath", "hang", "mom", "paint", "emotion", "quiet", "clear", 
"cloud", "few", "pretty", "bird", "outside", "paper", "picture", 
"front", "rock", "simple", "anyone", "meant", "reality", "road", 
"sense", "waste", "bit", "leaf", "thank", "happiness", "meet", "men",
"smoke", "truly", "decide", "self", "age", "book", "form", "alive", 
"carry", "escape", "damn", "instead", "able", "ice", "minute", 
"throw", "catch", "leg", "ring", "course", "goodbye", "lead", "poem",
"sick", "corner", "desire", "known", "problem", "remind", 
"shoulder", "suppose", "toward", "wave", "drink", "jump", "woman", 
"pretend", "sister", "week", "human", "joy", "crack", "grey", "pray",
"surprise", "dry", "knee", "less", "search", "bleed", "caught", 
"clean", "embrace", "future", "king", "son", "sorrow", "chest", 
"hug", "remain", "sat", "worth", "blow", "daddy", "final", "parent", 
"tight", "also", "create", "lonely", "safe", "cross", "dress", 
"evil", "silent", "bone", "fate", "perhaps", "anger", "class", 
"scar", "snow", "tiny", "tonight", "continue", "control", "dog", 
"edge", "mirror", "month", "suddenly", "comfort", "given", "loud", 
"quickly", "gaze", "plan", "rush", "stone", "town", "battle", 
"ignore", "spirit", "stood", "stupid", "yours", "brown", "build", 
"dust", "hey", "kept", "pay", "phone", "twist", "although", "ball", 
"beyond", "hidden", "nose", "taken", "fail", "float", "pure", 
"somehow", "wash", "wrap", "angry", "cheek", "creature", "forgotten",
"heat", "rip", "single", "space", "special", "weak", "whatever", 
"yell", "anyway", "blame", "job", "choose", "country", "curse", 
"drift", "echo", "figure", "grew", "laughter", "neck", "suffer", 
"worse", "yeah", "disappear", "foot", "forward", "knife", "mess", 
"somewhere", "stomach", "storm", "beg", "idea", "lift", "offer", 
"breeze", "field", "five", "often", "simply", "stuck", "win", 
"allow", "confuse", "enjoy", "except", "flower", "seek", "strength", 
"calm", "grin", "gun", "heavy", "hill", "large", "ocean", "shoe", 
"sigh", "straight", "summer", "tongue", "accept", "crazy", 
"everyday", "exist", "grass", "mistake", "sent", "shut", "surround", 
"table", "ache", "brain", "destroy", "heal", "nature", "shout", 
"sign", "stain", "choice", "doubt", "glance", "glow", "mountain", 
"queen", "stranger", "throat", "tomorrow", "city", "either", "fish", 
"flame", "rather", "shape", "spin", "spread", "ash", "distance", 
"finish", "image", "imagine", "important", "nobody", "shatter", 
"warmth", "became", "feed", "flesh", "funny", "lust", "shirt", 
"trouble", "yellow", "attention", "bare", "bite", "money", "protect",
"amaze", "appear", "born", "choke", "completely", "daughter", 
"fresh", "friendship", "gentle", "probably", "six", "deserve", 
"expect", "grab", "middle", "nightmare", "river", "thousand", 
"weight", "worst", "wound", "barely", "bottle", "cream", "regret", 
"relationship", "stick", "test", "crush", "endless", "fault", 
"itself", "rule", "spill", "art", "circle", "join", "kick", "mask", 
"master", "passion", "quick", "raise", "smooth", "unless", "wander", 
"actually", "broke", "chair", "deal", "favorite", "gift", "note", 
"number", "sweat", "box", "chill", "clothes", "lady", "mark", "park",
"poor", "sadness", "tie", "animal", "belong", "brush", "consume", 
"dawn", "forest", "innocent", "pen", "pride", "stream", "thick", 
"clay", "complete", "count", "draw", "faith", "press", "silver", 
"struggle", "surface", "taught", "teach", "wet", "bless", "chase", 
"climb", "enter", "letter", "melt", "metal", "movie", "stretch", 
"swing", "vision", "wife", "beside", "crash", "forgot", "guide", 
"haunt", "joke", "knock", "plant", "pour", "prove", "reveal", 
"steal", "stuff", "trip", "wood", "wrist", "bother", "bottom", 
"crawl", "crowd", "fix", "forgive", "frown", "grace", "loose", 
"lucky", "party", "release", "surely", "survive", "teacher", 
"gently", "grip", "speed", "suicide", "travel", "treat", "vein", 
"written", "cage", "chain", "conversation", "date", "enemy", 
"however", "interest", "million", "page", "pink", "proud", "sway", 
"themselves", "winter", "church", "cruel", "cup", "demon", 
"experience", "freedom", "pair", "pop", "purpose", "respect", 
"shoot", "softly", "state", "strange", "bar", "birth", "curl", 
"dirt", "excuse", "lord", "lovely", "monster", "order", "pack", 
"pants", "pool", "scene", "seven", "shame", "slide", "ugly", "among",
"blade", "blonde", "closet", "creek", "deny", "drug", "eternity", 
"gain", "grade", "handle", "key", "linger", "pale", "prepare", 
"swallow", "swim", "tremble", "wheel", "won", "cast", "cigarette", 
"claim", "college", "direction", "dirty", "gather", "ghost", 
"hundred", "loss", "lung", "orange", "present", "swear", "swirl", 
"twice", "wild", "bitter", "blanket", "doctor", "everywhere", 
"flash", "grown", "knowledge", "numb", "pressure", "radio", "repeat",
"ruin", "spend", "unknown", "buy", "clock", "devil", "early", 
"false", "fantasy", "pound", "precious", "refuse", "sheet", "teeth", 
"welcome", "add", "ahead", "block", "bury", "caress", "content", 
"depth", "despite", "distant", "marry", "purple", "threw", 
"whenever", "bomb", "dull", "easily", "grasp", "hospital", 
"innocence", "normal", "receive", "reply", "rhyme", "shade", 
"someday", "sword", "toe", "visit", "asleep", "bought", "center", 
"consider", "flat", "hero", "history", "ink", "insane", "muscle", 
"mystery", "pocket", "reflection", "shove", "silently", "smart", 
"soldier", "spot", "stress", "train", "type", "view", "whether", 
"bus", "energy", "explain", "holy", "hunger", "inch", "magic", "mix",
"noise", "nowhere", "prayer", "presence", "shock", "snap", "spider",
"study", "thunder", "trail", "admit", "agree", "bag", "bang", 
"bound", "butterfly", "cute", "exactly", "explode", "familiar", 
"fold", "further", "pierce", "reflect", "scent", "selfish", "sharp", 
"sink", "spring", "stumble", "universe", "weep", "women", 
"wonderful", "action", "ancient", "attempt", "avoid", "birthday", 
"branch", "chocolate", "core", "depress", "drunk", "especially", 
"focus", "fruit", "honest", "match", "palm", "perfectly", "pillow", 
"pity", "poison", "roar", "shift", "slightly", "thump", "truck", 
"tune", "twenty", "unable", "wipe", "wrote", "coat", "constant", 
"dinner", "drove", "egg", "eternal", "flight", "flood", "frame", 
"freak", "gasp", "glad", "hollow", "motion", "peer", "plastic", 
"root", "screen", "season", "sting", "strike", "team", "unlike", 
"victim", "volume", "warn", "weird", "attack", "await", "awake", 
"built", "charm", "crave", "despair", "fought", "grant", "grief", 
"horse", "limit", "message", "ripple", "sanity", "scatter", "serve", 
"split", "string", "trick", "annoy", "blur", "boat", "brave", 
"clearly", "cling", "connect", "fist", "forth", "imagination", 
"iron", "jock", "judge", "lesson", "milk", "misery", "nail", "naked",
"ourselves", "poet", "possible", "princess", "sail", "size", 
"snake", "society", "stroke", "torture", "toss", "trace", "wise", 
"bloom", "bullet", "cell", "check", "cost", "darling", "during", 
"footstep", "fragile", "hallway", "hardly", "horizon", "invisible", 
"journey", "midnight", "mud", "nod", "pause", "relax", "shiver", 
"sudden", "value", "youth", "abuse", "admire", "blink", "breast", 
"bruise", "constantly", "couple", "creep", "curve", "difference", 
"dumb", "emptiness", "gotta", "honor", "plain", "planet", "recall", 
"rub", "ship", "slam", "soar", "somebody", "tightly", "weather", 
"adore", "approach", "bond", "bread", "burst", "candle", "coffee", 
"cousin", "crime", "desert", "flutter", "frozen", "grand", "heel", 
"hello", "language", "level", "movement", "pleasure", "powerful", 
"random", "rhythm", "settle", "silly", "slap", "sort", "spoken", 
"steel", "threaten", "tumble", "upset", "aside", "awkward", "bee", 
"blank", "board", "button", "card", "carefully", "complain", "crap", 
"deeply", "discover", "drag", "dread", "effort", "entire", "fairy", 
"giant", "gotten", "greet", "illusion", "jeans", "leap", "liquid", 
"march", "mend", "nervous", "nine", "replace", "rope", "spine", 
"stole", "terror", "accident", "apple", "balance", "boom", 
"childhood", "collect", "demand", "depression", "eventually", 
"faint", "glare", "goal", "group", "honey", "kitchen", "laid", 
"limb", "machine", "mere", "mold", "murder", "nerve", "painful", 
"poetry", "prince", "rabbit", "shelter", "shore", "shower", "soothe",
"stair", "steady", "sunlight", "tangle", "tease", "treasure", 
"uncle", "begun", "bliss", "canvas", "cheer", "claw", "clutch", 
"commit", "crimson", "crystal", "delight", "doll", "existence", 
"express", "fog", "football", "gay", "goose", "guard", "hatred", 
"illuminate", "mass", "math", "mourn", "rich", "rough", "skip", 
"stir", "student", "style", "support", "thorn", "tough", "yard", 
"yearn", "yesterday", "advice", "appreciate", "autumn", "bank", 
"beam", "bowl", "capture", "carve", "collapse", "confusion", 
"creation", "dove", "feather", "girlfriend", "glory", "government", 
"harsh", "hop", "inner", "loser", "moonlight", "neighbor", "neither",
"peach", "pig", "praise", "screw", "shield", "shimmer", "sneak", 
"stab", "subject", "throughout", "thrown", "tower", "twirl", "wow", 
"army", "arrive", "bathroom", "bump", "cease", "cookie", "couch", 
"courage", "dim", "guilt", "howl", "hum", "husband", "insult", "led",
"lunch", "mock", "mostly", "natural", "nearly", "needle", "nerd", 
"peaceful", "perfection", "pile", "price", "remove", "roam", 
"sanctuary", "serious", "shiny", "shook", "sob", "stolen", "tap", 
"vain", "void", "warrior", "wrinkle", "affection", "apologize", 
"blossom", "bounce", "bridge", "cheap", "crumble", "decision", 
"descend", "desperately", "dig", "dot", "flip", "frighten", 
"heartbeat", "huge", "lazy", "lick", "odd", "opinion", "process", 
"puzzle", "quietly", "retreat", "score", "sentence", "separate", 
"situation", "skill", "soak", "square", "stray", "taint", "task", 
"tide", "underneath", "veil", "whistle", "anywhere", "bedroom", 
"bid", "bloody", "burden", "careful", "compare", "concern", 
"curtain", "decay", "defeat", "describe", "double", "dreamer", 
"driver", "dwell", "evening", "flare", "flicker", "grandma", 
"guitar", "harm", "horrible", "hungry", "indeed", "lace", "melody", 
"monkey", "nation", "object", "obviously", "rainbow", "salt", 
"scratch", "shown", "shy", "stage", "stun", "third", "tickle", 
"useless", "weakness", "worship", "worthless", "afternoon", "beard", 
"boyfriend", "bubble", "busy", "certain", "chin", "concrete", "desk",
"diamond", "doom", "drawn", "due", "felicity", "freeze", "frost", 
"garden", "glide", "harmony", "hopefully", "hunt", "jealous", 
"lightning", "mama", "mercy", "peel", "physical", "position", 
"pulse", "punch", "quit", "rant", "respond", "salty", "sane", 
"satisfy", "savior", "sheep", "slept", "social", "sport", "tuck", 
"utter", "valley", "wolf", "aim", "alas", "alter", "arrow", "awaken",
"beaten", "belief", "brand", "ceiling", "cheese", "clue", 
"confidence", "connection", "daily", "disguise", "eager", "erase", 
"essence", "everytime", "expression", "fan", "flag", "flirt", "foul",
"fur", "giggle", "glorious", "ignorance", "law", "lifeless", 
"measure", "mighty", "muse", "north", "opposite", "paradise", 
"patience", "patient", "pencil", "petal", "plate", "ponder", 
"possibly", "practice", "slice", "spell", "stock", "strife", "strip",
"suffocate", "suit", "tender", "tool", "trade", "velvet", "verse", 
"waist", "witch", "aunt", "bench", "bold", "cap", "certainly", 
"click", "companion", "creator", "dart", "delicate", "determine", 
"dish", "dragon", "drama", "drum", "dude", "everybody", "feast", 
"forehead", "former", "fright", "fully", "gas", "hook", "hurl", 
"invite", "juice", "manage", "moral", "possess", "raw", "rebel", 
"royal", "scale", "scary", "several", "slight", "stubborn", "swell", 
"talent", "tea", "terrible", "thread", "torment", "trickle", 
"usually", "vast", "violence", "weave", "acid", "agony", "ashamed", 
"awe", "belly", "blend", "blush", "character", "cheat", "common", 
"company", "coward", "creak", "danger", "deadly", "defense", 
"define", "depend", "desperate", "destination", "dew", "duck", 
"dusty", "embarrass", "engine", "example", "explore", "foe", 
"freely", "frustrate", "generation", "glove", "guilty", "health", 
"hurry", "idiot", "impossible", "inhale", "jaw", "kingdom", 
"mention", "mist", "moan", "mumble", "mutter", "observe", "ode", 
"pathetic", "pattern", "pie", "prefer", "puff", "rape", "rare", 
"revenge", "rude", "scrape", "spiral", "squeeze", "strain", "sunset",
"suspend", "sympathy", "thigh", "throne", "total", "unseen", 
"weapon", "weary");

};

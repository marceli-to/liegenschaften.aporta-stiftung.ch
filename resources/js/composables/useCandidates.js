import { ref, computed } from 'vue';
import { validateRequired, validateEmail } from '@/lib/utils';

function emptyCandidate() {
  return {
    salutation: null,
    name: null,
    firstname: null,
    email: null,
  };
}

/**
 * The recipients of an offer (create and edit form)
 */
export function useCandidates({ valid = false } = {}) {
  const candidates = ref([emptyCandidate()]);
  const isValid = ref(valid);

  function addCandidate() {
    candidates.value.push(emptyCandidate());
    isValid.value = false;
  }

  function removeCandidate() {
    candidates.value.pop();
    isValid.value = true;
  }

  function resetCandidates() {
    candidates.value = [emptyCandidate()];
  }

  function validate(event, candidate) {
    if (validateRequired(candidate.name) && validateRequired(candidate.firstname) && validateEmail(candidate.email)) {
      event.target.classList.remove('is-invalid');
      isValid.value = true;
      return true;
    }
    if (event.target.type == 'email' && validateEmail(event.target.value)) {
      event.target.classList.remove('is-invalid');
      return;
    }
    if (event.target.type == 'text' && validateRequired(event.target.value)) {
      event.target.classList.remove('is-invalid');
      return;
    }
    event.target.classList.add('is-invalid');
    isValid.value = false;
  }

  // «a», «a und b», «a, b und c» (the confirm dialog)
  const candidateList = computed(() => {
    const emails = candidates.value.map(candidate => candidate.email);
    if (emails.length <= 2) {
      return emails.join(' und ');
    }
    return `${emails.slice(0, -1).join(', ')} und ${emails[emails.length - 1]}`;
  });

  return { candidates, isValid, addCandidate, removeCandidate, resetCandidates, validate, candidateList };
}
